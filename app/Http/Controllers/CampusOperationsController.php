<?php

namespace App\Http\Controllers;

use App\Models\{HostelAllocation, HostelBuilding, HostelRoom, LibraryItem, LibraryLoan, MarketplaceOrder, MarketplaceProduct, MarketplaceVendor, Student, TransportAssignment, TransportRoute, TransportVehicle, Wallet};
use App\Services\Campus\{MarketplaceService, WalletService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CampusOperationsController extends Controller
{
    private function schoolId(): int { return (int) Auth::user()->school_id; }
    private function exists(string $table) { return Rule::exists($table, 'id')->where('school_id', $this->schoolId()); }

    public function index()
    {
        $id = $this->schoolId();
        return view('campus-operations.index', [
            'students' => Student::where('school_id', $id)->orderBy('first_name')->get(),
            'buildings' => HostelBuilding::where('school_id', $id)->with('rooms')->get(),
            'allocations' => HostelAllocation::where('school_id', $id)->with(['student','room.building'])->latest()->limit(30)->get(),
            'libraryItems' => LibraryItem::where('school_id', $id)->orderBy('title')->get(),
            'loans' => LibraryLoan::where('school_id', $id)->with(['student','item'])->latest()->limit(30)->get(),
            'routes' => TransportRoute::where('school_id', $id)->get(), 'vehicles' => TransportVehicle::where('school_id', $id)->get(),
            'assignments' => TransportAssignment::where('school_id', $id)->with(['student','route','vehicle'])->latest()->limit(30)->get(),
            'wallets' => Wallet::where('school_id', $id)->with('student')->get(),
            'vendors' => MarketplaceVendor::where('school_id', $id)->get(), 'products' => MarketplaceProduct::where('school_id', $id)->get(),
            'orders' => MarketplaceOrder::where('school_id', $id)->with(['student','items'])->latest()->limit(30)->get(),
        ]);
    }

    public function storeBuilding(Request $r) { $d=$r->validate(['name'=>'required|string|max:255','gender'=>['required',Rule::in(['male','female','mixed'])]]); HostelBuilding::create($d+['school_id'=>$this->schoolId()]); return back()->with('success','Hostel created.'); }
    public function storeRoom(Request $r) { $d=$r->validate(['hostel_building_id'=>['required',$this->exists('hostel_buildings')],'room_number'=>'required|string|max:50','capacity'=>'required|integer|min:1|max:100','fee'=>'nullable|numeric|min:0']); HostelRoom::create($d+['school_id'=>$this->schoolId()]); return back()->with('success','Room created.'); }
    public function allocateRoom(Request $r) {
        $d=$r->validate(['hostel_room_id'=>['required',$this->exists('hostel_rooms')],'student_id'=>['required',$this->exists('students')],'academic_term_id'=>['nullable',$this->exists('academic_terms')],'starts_on'=>'required|date','ends_on'=>'nullable|date|after_or_equal:starts_on']);
        DB::transaction(function() use($d){$room=HostelRoom::lockForUpdate()->findOrFail($d['hostel_room_id']); if($room->allocations()->where('status','active')->count()>=$room->capacity) throw ValidationException::withMessages(['hostel_room_id'=>'This room is full.']); HostelAllocation::create($d+['school_id'=>$this->schoolId(),'allocated_by'=>Auth::id(),'status'=>'active']);});
        return back()->with('success','Student allocated.');
    }
    public function storeLibraryItem(Request $r) { $d=$r->validate(['title'=>'required|string|max:255','author'=>'nullable|string|max:255','isbn'=>'nullable|string|max:50','barcode'=>'required|string|max:100','category'=>'nullable|string|max:100','copies_total'=>'required|integer|min:1']); LibraryItem::create($d+['school_id'=>$this->schoolId(),'copies_available'=>$d['copies_total']]); return back()->with('success','Library item added.'); }
    public function checkout(Request $r) {
        $d=$r->validate(['library_item_id'=>['required',$this->exists('library_items')],'student_id'=>['required',$this->exists('students')],'due_on'=>'required|date|after:today']);
        DB::transaction(function()use($d){$item=LibraryItem::lockForUpdate()->findOrFail($d['library_item_id']); if($item->copies_available<1) throw ValidationException::withMessages(['library_item_id'=>'No copy is available.']); $item->decrement('copies_available'); LibraryLoan::create($d+['school_id'=>$this->schoolId(),'issued_by'=>Auth::id(),'borrowed_at'=>now(),'status'=>'borrowed']);});
        return back()->with('success','Item checked out.');
    }
    public function returnLoan(LibraryLoan $loan) { abort_unless($loan->school_id===$this->schoolId(),404); if(!$loan->returned_at) DB::transaction(function()use($loan){$loan->update(['returned_at'=>now(),'status'=>'returned']); $loan->item()->lockForUpdate()->first()->increment('copies_available');}); return back()->with('success','Item returned.'); }
    public function storeRoute(Request $r) { $d=$r->validate(['name'=>'required|string|max:255','code'=>'required|string|max:30','start_point'=>'required|string|max:255','end_point'=>'required|string|max:255','term_fee'=>'nullable|numeric|min:0']); TransportRoute::create($d+['school_id'=>$this->schoolId()]); return back()->with('success','Route created.'); }
    public function storeVehicle(Request $r) { $d=$r->validate(['registration_number'=>'required|string|max:50','make_model'=>'nullable|string|max:255','capacity'=>'required|integer|min:1','driver_name'=>'required|string|max:255','driver_phone'=>'required|string|max:30']); TransportVehicle::create($d+['school_id'=>$this->schoolId()]); return back()->with('success','Vehicle created.'); }
    public function assignTransport(Request $r) {
        $d=$r->validate(['student_id'=>['required',$this->exists('students')],'transport_route_id'=>['required',$this->exists('transport_routes')],'transport_vehicle_id'=>['nullable',$this->exists('transport_vehicles')],'academic_term_id'=>['nullable',$this->exists('academic_terms')],'pickup_stop'=>'required|string|max:255','dropoff_stop'=>'required|string|max:255']);
        DB::transaction(function()use($d){if(!empty($d['transport_vehicle_id'])){$v=TransportVehicle::lockForUpdate()->findOrFail($d['transport_vehicle_id']); if($v->assignments()->where('status','active')->count()>=$v->capacity) throw ValidationException::withMessages(['transport_vehicle_id'=>'This vehicle is full.']);} TransportAssignment::create($d+['school_id'=>$this->schoolId(),'status'=>'active']);}); return back()->with('success','Transport assigned.');
    }
    public function walletTransaction(Request $r, WalletService $service) { $d=$r->validate(['student_id'=>['required',$this->exists('students')],'type'=>['required',Rule::in(['credit','debit'])],'amount'=>'required|numeric|min:0.01','reference'=>'required|string|max:100','description'=>'required|string|max:255']); $wallet=Wallet::firstOrCreate(['school_id'=>$this->schoolId(),'student_id'=>$d['student_id']],['currency'=>'NGN']); $service->transact($wallet,$d['type'],(float)$d['amount'],$d['reference'],$d['description'],Auth::id()); return back()->with('success','Wallet updated.'); }
    public function storeVendor(Request $r) { $d=$r->validate(['name'=>'required|string|max:255','email'=>'nullable|email','phone'=>'nullable|string|max:30']); MarketplaceVendor::create($d+['school_id'=>$this->schoolId()]); return back()->with('success','Vendor created.'); }
    public function storeProduct(Request $r) { $d=$r->validate(['marketplace_vendor_id'=>['nullable',$this->exists('marketplace_vendors')],'name'=>'required|string|max:255','sku'=>'required|string|max:80','description'=>'nullable|string','price'=>'required|numeric|min:0','stock_quantity'=>'required|integer|min:0']); MarketplaceProduct::create($d+['school_id'=>$this->schoolId()]); return back()->with('success','Product created.'); }
    public function storeOrder(Request $r, MarketplaceService $service) { $d=$r->validate(['student_id'=>['required',$this->exists('students')],'items'=>'required|array|min:1','items.*.product_id'=>['required',$this->exists('marketplace_products')],'items.*.quantity'=>'required|integer|min:1']); $service->order($this->schoolId(),$d['student_id'],$d['items'],Auth::id()); return back()->with('success','Order paid from wallet.'); }
}
