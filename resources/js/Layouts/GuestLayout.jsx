import React, { useState } from "react";

export default function GuestLayout({ children }) {
    const [menuOpen, setMenuOpen] = useState(false);
    return (
        <div className="schoolos-site min-h-screen bg-[#f7f8f3] text-[#142b27] antialiased">
            <nav className="site-nav" aria-label="Main navigation">
                <div className="site-shell flex h-[76px] items-center justify-between">
                    <a
                        href="/"
                        className="relative z-10"
                        aria-label="SchoolOS home"
                    >
                        <img
                            src="/images/logo.png"
                            alt="Plus36 SchoolOS"
                            className="h-12 w-auto brightness-0 invert"
                        />
                    </a>
                    <div className="hidden items-center gap-8 lg:flex">
                        {["product", "solutions", "pricing", "faq"].map(
                            (item) => (
                                <a
                                    key={item}
                                    className="nav-link capitalize"
                                    href={`#${item}`}
                                >
                                    {item}
                                </a>
                            ),
                        )}
                    </div>
                    <div className="hidden items-center gap-3 sm:flex">
                        <a
                            href={route("login")}
                            className="rounded-full px-5 py-2.5 text-sm font-bold text-white hover:bg-white/10"
                        >
                            Log in
                        </a>
                        <a href={route("register")} className="nav-cta">
                            Start free trial
                        </a>
                    </div>
                    <button
                        type="button"
                        className="relative z-10 grid h-11 w-11 place-items-center rounded-full border border-white/20 text-white sm:hidden"
                        aria-expanded={menuOpen}
                        aria-controls="mobile-menu"
                        aria-label={
                            menuOpen ? "Close navigation" : "Open navigation"
                        }
                        onClick={() => setMenuOpen(!menuOpen)}
                    >
                        <span className="text-2xl">{menuOpen ? "×" : "≡"}</span>
                    </button>
                </div>
                {menuOpen && (
                    <div id="mobile-menu" className="mobile-menu sm:hidden">
                        {["product", "solutions", "pricing", "faq"].map(
                            (item) => (
                                <a
                                    key={item}
                                    href={`#${item}`}
                                    onClick={() => setMenuOpen(false)}
                                    className="capitalize"
                                >
                                    {item}
                                </a>
                            ),
                        )}
                        <div className="mt-2 grid grid-cols-2 gap-3 border-t border-white/10 pt-5">
                            <a
                                href={route("login")}
                                className="mobile-secondary"
                            >
                                Log in
                            </a>
                            <a
                                href={route("register")}
                                className="mobile-primary"
                            >
                                Start free
                            </a>
                        </div>
                    </div>
                )}
            </nav>
            <main>{children}</main>
            <footer className="bg-[#082f2a] text-white">
                <div className="site-shell py-14">
                    <div className="grid gap-10 border-b border-white/10 pb-12 md:grid-cols-[1.4fr_1fr_1fr]">
                        <div>
                            <img
                                src="/images/logo.png"
                                alt="Plus36 SchoolOS"
                                className="mb-5 h-14 w-auto brightness-0 invert"
                            />
                            <p className="max-w-sm text-sm leading-7 text-white/60">
                                One connected operating system for academics,
                                finance, communication and student success.
                            </p>
                        </div>
                        <div>
                            <p className="mb-4 text-xs font-bold uppercase tracking-[.18em] text-[#7ed3c4]">
                                Explore
                            </p>
                            <div className="grid gap-3 text-sm text-white/70">
                                <a href="#product">Product</a>
                                <a href="#solutions">Solutions</a>
                                <a href="#pricing">Pricing</a>
                            </div>
                        </div>
                        <div>
                            <p className="mb-4 text-xs font-bold uppercase tracking-[.18em] text-[#7ed3c4]">
                                Get in touch
                            </p>
                            <a
                                href="mailto:support@plus36networks.com"
                                className="text-sm text-white/70"
                            >
                                info@plus36networks.tech
                            </a>
                        </div>
                    </div>
                    <div className="flex flex-col gap-4 pt-7 text-xs text-white/45 sm:flex-row sm:justify-between">
                        <p>© 2026 Plus36 Networks. All rights reserved.</p>
                        <p>Built for modern African institutions.</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}
