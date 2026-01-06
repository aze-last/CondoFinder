# CondoFinder Project Plan

## Goals
- Deliver a production-ready condo showroom SaaS with public showroom links per owner and connected dashboards for owners/admin.
- Ensure reliability (tests passing, migrations clean), strong UX, and clear pricing options for sales.

## Current State
- Public showroom live per owner via `public_key/public_slug`.
- Dashboard and listing flows working; viewing requests and inquiries wired.
- Welcome page includes live showroom preview and branding.

## Pricing Models (for sales/marketing)
- **One-Time Sale**
  - Basic ₱40,000: Booking system, property listings, payment integration, admin dashboard.
  - Premium ₱80,000: Basic + mobile app, advanced analytics, custom branding.
  - Enterprise ₱150,000+: Multi-property management, API integrations, white-label.
- **Subscription (Preferred for recurring)**
  - Monthly ₱3,000–₱8,000: Hosting, maintenance, updates, support.
  - Annual ₱30,000–₱80,000: 2–3 months discount, priority support.

## Near-Term Tasks
- Fix remaining Flux component gaps and Livewire warnings (ensure consistent toasts/alerts).
.- Verify media loading for listings and showroom; ensure storage symlink exists in prod.
- Run full test suite and address any lingering failures.
- Add customer/admin notifications:
  - Customer: after “Request Schedule,” show “Request sent. Wait for the Condo Admin to send a message.”
  - Admin: after approving, prompt to text the client to confirm.

## Optional Enhancements
- Add share/copy link UI for showroom and listings in dashboard.
- Add analytics cards (views, inquiries conversion) on dashboard once data is stable.
- Refine welcome page pricing section to match models above.

## Deployment Checklist
- Migrations clean (no duplicate public keys/slugs).
- Storage symlink present; media disks configured.
- Env configured for queues/mail; cache/config cleared.
- Basic smoke: login, create listing, upload media, submit inquiry/viewing, approve request, public showroom loads.
