<?php

namespace Tests\Feature;

use App\Models\{HostelBuilding, HostelRoom, LibraryItem, MarketplaceProduct, School, Student, User, Wallet};
use App\Services\Campus\{MarketplaceService, WalletService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CampusOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_is_idempotent_and_cannot_overdraw(): void
    {
        $school=School::factory()->create(); $student=Student::factory()->create(['school_id'=>$school->id]); $admin=User::factory()->create(['school_id'=>$school->id]);
        $wallet=Wallet::create(['school_id'=>$school->id,'student_id'=>$student->id,'currency'=>'NGN']); $service=app(WalletService::class);
        $first=$service->transact($wallet,'credit',5000,'deposit-1','Deposit',$admin->id); $again=$service->transact($wallet,'credit',5000,'deposit-1','Deposit',$admin->id);
        $this->assertSame($first->id,$again->id); $this->assertSame('5000.00',$wallet->fresh()->balance);
        $this->expectException(ValidationException::class); $service->transact($wallet,'debit',5001,'debit-1','Purchase',$admin->id);
    }

    public function test_hostel_and_library_capacity_are_enforced(): void
    {
        $school=School::factory()->create(); $admin=User::factory()->create(['school_id'=>$school->id,'role'=>'school_admin']); $students=Student::factory()->count(2)->create(['school_id'=>$school->id]);
        $building=HostelBuilding::create(['school_id'=>$school->id,'name'=>'A','gender'=>'mixed']); $room=HostelRoom::create(['school_id'=>$school->id,'hostel_building_id'=>$building->id,'room_number'=>'1','capacity'=>1]);
        $this->actingAs($admin)->post(route('campus.allocations.store'),['hostel_room_id'=>$room->id,'student_id'=>$students[0]->id,'starts_on'=>today()->toDateString()])->assertSessionHasNoErrors();
        $this->post(route('campus.allocations.store'),['hostel_room_id'=>$room->id,'student_id'=>$students[1]->id,'starts_on'=>today()->toDateString()])->assertSessionHasErrors('hostel_room_id');
        $item=LibraryItem::create(['school_id'=>$school->id,'title'=>'Book','barcode'=>'B1','copies_total'=>1,'copies_available'=>1]);
        $this->post(route('campus.library.loans.store'),['library_item_id'=>$item->id,'student_id'=>$students[0]->id,'due_on'=>today()->addWeek()->toDateString()])->assertSessionHasNoErrors();
        $this->post(route('campus.library.loans.store'),['library_item_id'=>$item->id,'student_id'=>$students[1]->id,'due_on'=>today()->addWeek()->toDateString()])->assertSessionHasErrors('library_item_id');
    }

    public function test_marketplace_order_atomically_debits_wallet_and_stock(): void
    {
        $school=School::factory()->create(); $student=Student::factory()->create(['school_id'=>$school->id]); $admin=User::factory()->create(['school_id'=>$school->id]);
        $wallet=Wallet::create(['school_id'=>$school->id,'student_id'=>$student->id,'currency'=>'NGN']); app(WalletService::class)->transact($wallet,'credit',1000,'fund','Funding',$admin->id);
        $product=MarketplaceProduct::create(['school_id'=>$school->id,'name'=>'Lunch','sku'=>'L1','price'=>250,'stock_quantity'=>5]);
        $order=app(MarketplaceService::class)->order($school->id,$student->id,[['product_id'=>$product->id,'quantity'=>2]],$admin->id);
        $this->assertSame('paid',$order->status); $this->assertSame('500.00',$wallet->fresh()->balance); $this->assertSame(3,$product->fresh()->stock_quantity);
    }
}
