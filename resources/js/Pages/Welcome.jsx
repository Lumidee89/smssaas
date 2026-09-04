import React, { useEffect, useRef, useState } from "react";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head } from "@inertiajs/react";

const products = [
    {
        eyebrow: "Academic command centre",
        title: "Run every school day from one calm workspace.",
        copy: "Move from admissions to attendance, assessments and verified transcripts without scattered spreadsheets or duplicate records.",
        stats: [
            ["94%", "Attendance"],
            ["12", "Classes today"],
            ["8", "Needs attention"],
        ],
        tab: "Academics",
        tone: "mint",
    },
    {
        eyebrow: "Finance, finally reconciled",
        title: "Know what is paid, overdue and coming next.",
        copy: "Create fee invoices, collect through Paystack, manage scholarships and installments, and keep a clean audit trail for every transaction.",
        stats: [
            ["₦8.4m", "Collected"],
            ["82%", "Paid on time"],
            ["₦1.2m", "Outstanding"],
        ],
        tab: "Finance",
        tone: "gold",
    },
    {
        eyebrow: "Parents stay in the loop",
        title: "Turn every update into trusted communication.",
        copy: "Give families a mobile window into results, balances, announcements and direct school messages—with timely push notifications.",
        stats: [
            ["1,248", "Parents active"],
            ["96%", "Messages read"],
            ["4.8/5", "Family rating"],
        ],
        tab: "Parent app",
        tone: "blue",
    },
    {
        eyebrow: "Student success intelligence",
        title: "See who needs support before they fall behind.",
        copy: "Bring academic, attendance and development signals together, then assign interventions your team can act on and track.",
        stats: [
            ["24", "At-risk signals"],
            ["18", "Interventions"],
            ["+14%", "Trend this term"],
        ],
        tab: "Analytics",
        tone: "coral",
    },
];
const plans = [
    {
        name: "Starter",
        price: "₦25,000",
        audience: "For growing schools",
        limits: "Up to 300 students · 30 staff",
        features: ["Academics & attendance", "Finance management", "Messaging"],
        cta: "Start free trial",
    },
    {
        name: "Growth",
        price: "₦60,000",
        audience: "For established schools",
        limits: "Up to 1,500 students · 150 staff",
        features: [
            "Everything in Starter",
            "Parent mobile app",
            "Analytics & CBT",
        ],
        cta: "Choose Growth",
        popular: true,
    },
    {
        name: "University",
        price: "₦150,000",
        audience: "For large institutions",
        limits: "Up to 10,000 students · 1,000 staff",
        features: ["Everything in Growth", "AI teaching copilot", "API access"],
        cta: "Choose University",
    },
    {
        name: "Enterprise",
        price: "Custom",
        audience: "For school groups",
        limits: "Custom students & staff",
        features: [
            "All SchoolOS features",
            "White-label experience",
            "Priority support",
        ],
        cta: "Talk to sales",
        sales: true,
    },
];
const faqs = [
    [
        "Can we try SchoolOS before paying?",
        "Yes. Create a school account to begin your free trial and explore the core workflows with your team.",
    ],
    [
        "Does SchoolOS support Nigerian payments?",
        "Yes. SchoolOS supports NGN billing and Paystack-powered online collections, alongside offline payment records and receipts.",
    ],
    [
        "Is there a parent experience?",
        "Yes. Growth plans and above include the parent mobile app for results, balances, announcements, notifications and direct messaging.",
    ],
    [
        "Can universities use SchoolOS?",
        "Yes. The University plan supports up to 10,000 students and 1,000 staff, with CBT, analytics, AI features and API access.",
    ],
];

function ProductPreview({ product }) {
    return (
        <div className={`product-preview product-preview--${product.tone}`}>
            <div className="preview-topbar">
                <div className="flex items-center gap-2">
                    <span className="preview-mark">S</span>
                    <span className="text-xs font-bold">SchoolOS</span>
                </div>
                <div className="preview-avatar">AO</div>
            </div>
            <div className="preview-body">
                <aside className="preview-sidebar" aria-hidden="true">
                    {[1, 2, 3, 4, 5].map((n) => (
                        <span
                            key={n}
                            className={n === 2 ? "active" : ""}
                        ></span>
                    ))}
                </aside>
                <div className="min-w-0 flex-1">
                    <div className="mb-5 flex items-end justify-between gap-3">
                        <div>
                            <p className="text-[10px] text-slate-400">
                                Good morning, Amara
                            </p>
                            <h3 className="text-lg font-bold">
                                {product.tab} overview
                            </h3>
                        </div>
                        <button className="preview-button" type="button">
                            + New record
                        </button>
                    </div>
                    <div className="grid grid-cols-3 gap-2.5">
                        {product.stats.map(([value, label]) => (
                            <div className="preview-stat" key={label}>
                                <strong>{value}</strong>
                                <span>{label}</span>
                            </div>
                        ))}
                    </div>
                    <div className="preview-chart mt-4">
                        <div className="flex justify-between">
                            <span className="text-[10px] font-bold">
                                Term performance
                            </span>
                            <span className="text-[9px] text-slate-400">
                                Last 8 weeks
                            </span>
                        </div>
                        <div className="chart-bars" aria-hidden="true">
                            {[42, 61, 48, 72, 64, 86, 78, 94].map((h, i) => (
                                <span
                                    key={i}
                                    style={{ height: `${h}%` }}
                                ></span>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default function Welcome() {
    const [activeProduct, setActiveProduct] = useState(0);
    const [openFaq, setOpenFaq] = useState(0);
    const touchStart = useRef(null);
    useEffect(() => {
        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches)
            return;
        const timer = window.setInterval(
            () => setActiveProduct((n) => (n + 1) % products.length),
            6500,
        );
        return () => window.clearInterval(timer);
    }, []);
    const moveProduct = (direction) =>
        setActiveProduct(
            (n) => (n + direction + products.length) % products.length,
        );
    return (
        <GuestLayout>
            <Head title="SchoolOS — One operating system for your school">
                <meta
                    name="description"
                    content="SchoolOS connects academics, finance, communication, CBT and student success in one school management platform."
                />
            </Head>
            <section className="hero-section">
                <div className="hero-grid" aria-hidden="true"></div>
                <div className="site-shell relative z-10 grid items-center gap-14 pb-20 pt-36 lg:grid-cols-[1.02fr_.98fr] lg:pb-28 lg:pt-44">
                    <div>
                        <div className="eyebrow eyebrow-light">
                            <span></span>Built for schools that are moving
                            forward
                        </div>
                        <h1 className="hero-title">
                            Your entire school.
                            <br />
                            <em>One intelligent system.</em>
                        </h1>
                        <p className="mt-7 max-w-xl text-base leading-8 text-white/68 sm:text-lg">
                            SchoolOS connects academics, payments, parents and
                            performance—so your team spends less time chasing
                            records and more time helping students thrive.
                        </p>
                        <div className="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a
                                href={route("register")}
                                className="button-primary"
                            >
                                Start your free trial <span>→</span>
                            </a>
                            <a href="#product" className="button-ghost">
                                Explore the platform
                            </a>
                        </div>
                        {/* <div className="mt-9 flex flex-wrap gap-x-6 gap-y-2 text-xs font-semibold text-white/55">
                            <span>✓ No credit card required</span>
                            <span>✓ Set up in minutes</span>
                            <span>✓ Built for NGN payments</span>
                        </div> */}
                    </div>
                    <div className="hero-visual">
                        <div className="hero-orbit hero-orbit-one"></div>
                        <div className="hero-orbit hero-orbit-two"></div>
                        <ProductPreview product={products[0]} />
                        <div className="float-card float-card-top">
                            <span className="pulse-dot"></span>
                            <div>
                                <strong>Payment received</strong>
                                <small>₦185,000 · just now</small>
                            </div>
                        </div>
                        <div className="float-card float-card-bottom">
                            <span className="float-score">92</span>
                            <div>
                                <strong>Student success</strong>
                                <small>On track this term</small>
                            </div>
                        </div>
                    </div>
                </div>
                {/* <div className="hero-trust">
                    <span>ACADEMICS</span>
                    <i></i>
                    <span>FINANCE</span>
                    <i></i>
                    <span>CBT</span>
                    <i></i>
                    <span>PARENT APP</span>
                    <i></i>
                    <span>ANALYTICS</span>
                </div> */}
            </section>

            <section
                id="product"
                className="section-pad overflow-hidden bg-[#f7f8f3]"
            >
                <div className="site-shell">
                    <div className="mb-12 grid gap-5 lg:grid-cols-2 lg:items-end">
                        <div>
                            <div className="eyebrow">
                                <span></span>One connected platform
                            </div>
                            <h2 className="section-title">
                                Everything works better
                                <br />
                                when it works together.
                            </h2>
                        </div>
                        <p className="max-w-lg text-base leading-7 text-[#5f706b] lg:justify-self-end">
                            Every module shares the same reliable student
                            record, giving leaders one source of truth and every
                            team the context they need.
                        </p>
                    </div>
                    <div
                        className="product-tabs"
                        role="tablist"
                        aria-label="SchoolOS products"
                    >
                        {products.map((product, index) => (
                            <button
                                key={product.tab}
                                type="button"
                                role="tab"
                                aria-selected={activeProduct === index}
                                className={
                                    activeProduct === index ? "active" : ""
                                }
                                onClick={() => setActiveProduct(index)}
                            >
                                {product.tab}
                            </button>
                        ))}
                    </div>
                    <div
                        className="product-stage"
                        onTouchStart={(e) => {
                            touchStart.current = e.touches[0].clientX;
                        }}
                        onTouchEnd={(e) => {
                            const d =
                                e.changedTouches[0].clientX -
                                touchStart.current;
                            if (Math.abs(d) > 45) moveProduct(d > 0 ? -1 : 1);
                            touchStart.current = null;
                        }}
                    >
                        <div
                            className="product-copy"
                            key={`copy-${activeProduct}`}
                        >
                            <div className="eyebrow">
                                <span></span>
                                {products[activeProduct].eyebrow}
                            </div>
                            <h3>{products[activeProduct].title}</h3>
                            <p>{products[activeProduct].copy}</p>
                            <a href={route("register")} className="text-link">
                                See it in action <span>↗</span>
                            </a>
                            <div className="mt-10 flex gap-2">
                                <button
                                    className="round-control"
                                    type="button"
                                    onClick={() => moveProduct(-1)}
                                    aria-label="Previous product"
                                >
                                    ←
                                </button>
                                <button
                                    className="round-control"
                                    type="button"
                                    onClick={() => moveProduct(1)}
                                    aria-label="Next product"
                                >
                                    →
                                </button>
                            </div>
                        </div>
                        <div
                            className="product-window"
                            key={`preview-${activeProduct}`}
                        >
                            <ProductPreview product={products[activeProduct]} />
                        </div>
                    </div>
                </div>
            </section>

            <section id="solutions" className="section-pad bg-white">
                <div className="site-shell">
                    <div className="max-w-2xl">
                        <div className="eyebrow">
                            <span></span>Designed for the whole community
                        </div>
                        <h2 className="section-title">
                            One system. A better day for everyone.
                        </h2>
                    </div>
                    <div className="mt-12 grid gap-px overflow-hidden rounded-[28px] border border-[#dce5df] bg-[#dce5df] md:grid-cols-3">
                        {[
                            [
                                "01",
                                "School leaders",
                                "See enrolment, revenue, operations and risk in one clear view—then make decisions with confidence.",
                            ],
                            [
                                "02",
                                "Teachers & staff",
                                "Take attendance, manage assessments, run CBT and publish results without repeating work.",
                            ],
                            [
                                "03",
                                "Parents & students",
                                "Stay current on results, fees, announcements and school conversations from anywhere.",
                            ],
                        ].map(([n, title, copy]) => (
                            <article className="audience-card" key={n}>
                                <span>{n}</span>
                                <h3>{title}</h3>
                                <p>{copy}</p>
                                <a href="#product">
                                    Explore <b>→</b>
                                </a>
                            </article>
                        ))}
                    </div>
                </div>
            </section>

            <section className="outcomes-section">
                <div className="site-shell grid gap-12 lg:grid-cols-[.8fr_1.2fr] lg:items-center">
                    <div>
                        <div className="eyebrow eyebrow-light">
                            <span></span>Clarity at every level
                        </div>
                        <h2 className="section-title text-white">
                            From daily tasks to the bigger picture.
                        </h2>
                        <p className="mt-6 max-w-md leading-7 text-white/60">
                            SchoolOS turns the work your team already does into
                            useful, connected intelligence.
                        </p>
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        {[
                            [
                                "01",
                                "One student record",
                                "Academics, finance, attendance and family context stay connected.",
                            ],
                            [
                                "02",
                                "Built-in accountability",
                                "Approvals, permissions and audit trails keep work dependable.",
                            ],
                            [
                                "03",
                                "Actionable insights",
                                "Spot student risk and financial trends while there is time to act.",
                            ],
                            [
                                "04",
                                "Ready to grow",
                                "Support a school, a university or a multi-campus institution.",
                            ],
                        ].map(([n, title, copy]) => (
                            <div className="outcome-card" key={n}>
                                <span>{n}</span>
                                <h3>{title}</h3>
                                <p>{copy}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            <section id="pricing" className="section-pad bg-[#f7f8f3]">
                <div className="site-shell">
                    <div className="text-center">
                        <div className="eyebrow justify-center">
                            <span></span>Simple, transparent pricing
                        </div>
                        <h2 className="section-title">
                            A plan for where you are.
                            <br />
                            Room for where you’re going.
                        </h2>
                        <p className="mx-auto mt-5 max-w-xl text-[#65746f]">
                            All prices are billed monthly in Nigerian naira.
                            Start with the plan that fits your institution
                            today.
                        </p>
                    </div>
                    <div className="mt-14 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        {plans.map((plan) => (
                            <article
                                key={plan.name}
                                className={`price-card ${plan.popular ? "price-card-popular" : ""}`}
                            >
                                {plan.popular && (
                                    <div className="popular-label">
                                        Most popular
                                    </div>
                                )}
                                <p className="text-sm font-bold">{plan.name}</p>
                                <p className="mt-2 text-xs text-[#70807b]">
                                    {plan.audience}
                                </p>
                                <div className="mt-7">
                                    <strong>{plan.price}</strong>
                                    {plan.price !== "Custom" && (
                                        <span>/month</span>
                                    )}
                                </div>
                                <p className="price-limits">{plan.limits}</p>
                                <ul>
                                    {plan.features.map((f) => (
                                        <li key={f}>
                                            <i>✓</i>
                                            {f}
                                        </li>
                                    ))}
                                </ul>
                                <a
                                    href={
                                        plan.sales
                                            ? "mailto:support@plus36networks.com?subject=SchoolOS%20Enterprise%20plan"
                                            : route("register")
                                    }
                                    className={
                                        plan.popular
                                            ? "price-button price-button-filled"
                                            : "price-button"
                                    }
                                >
                                    {plan.cta}
                                </a>
                            </article>
                        ))}
                    </div>
                </div>
            </section>

            <section id="faq" className="section-pad bg-white">
                <div className="site-shell grid gap-12 lg:grid-cols-[.7fr_1.3fr]">
                    <div>
                        <div className="eyebrow">
                            <span></span>Questions, answered
                        </div>
                        <h2 className="section-title">Good to know.</h2>
                        <p className="mt-5 max-w-sm leading-7 text-[#65746f]">
                            Need a more specific answer?{" "}
                            <a
                                className="font-bold text-[#17675d] underline"
                                href="mailto:support@plus36networks.com"
                            >
                                Talk to our team.
                            </a>
                        </p>
                    </div>
                    <div className="divide-y divide-[#dce5df] border-y border-[#dce5df]">
                        {faqs.map(([q, a], i) => (
                            <div key={q} className="faq-item">
                                <button
                                    type="button"
                                    aria-expanded={openFaq === i}
                                    onClick={() =>
                                        setOpenFaq(openFaq === i ? -1 : i)
                                    }
                                >
                                    <span>{q}</span>
                                    <i>{openFaq === i ? "−" : "+"}</i>
                                </button>
                                <div
                                    className={
                                        openFaq === i
                                            ? "faq-answer open"
                                            : "faq-answer"
                                    }
                                >
                                    <p>{a}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>
            <section className="cta-section">
                <div className="site-shell relative z-10 text-center">
                    <p className="eyebrow eyebrow-light justify-center">
                        <span></span>Your next school day can feel different
                    </p>
                    <h2>
                        Bring your school
                        <br />
                        into one clear view.
                    </h2>
                    <p>Start your free SchoolOS trial today.</p>
                    <a href={route("register")} className="button-primary mt-8">
                        Create your school account <span>→</span>
                    </a>
                </div>
            </section>
        </GuestLayout>
    );
}
