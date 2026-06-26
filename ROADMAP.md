# SetG — EDO Community Portal · Build Roadmap

A private, members-only WordPress portal for SETG's EDO-team.
Built **phase by phase**. Each phase ends with a **manual test checklist** the
client/owner runs before the next phase starts.

- **Design:** approved Claude Design prototype — style **Rustig (light)**.
- **UI language:** Dutch.
- **Plugin:** `SetG` (folder `setg-edo-portal/`, main file `setg.php`, text domain `setg`).
- **Portal URL:** `/portal/` and `/portal/{view}`.

---

## Phase 0 — Foundation & app shell  *(in progress)*

The skeleton everything else hangs on.

- Plugin bootstrap, header, activation/deactivation (flush rewrites, create role).
- Data models — custom post types: `edo_assignment`, `edo_training`,
  `edo_document`, `edo_announcement` (+ their meta fields).
- Member role `edo_member` + access capability; portal is members-only.
- Front-end routing + standalone full-page **app shell** (sidebar, top bar,
  mobile tabs) bypassing the theme.
- Assets: Plus Jakarta Sans + `portal.css` (Rustig theme).
- **Login screen** (authenticates via core WordPress).
- **Dashboard** view (stats + upcoming assignments + announcements), using
  sample content until real content is added.

**✅ Check at end of Phase 0**
1. Plugin activates with no errors (Plugins screen shows **SetG**).
2. Visiting `/portal/` while logged out shows the styled **login** screen.
3. Logging in as admin lands on the **Dashboard** — matches the Rustig design.
4. Sidebar menu items render; clicking them changes the URL (unbuilt screens
   show a tidy "in aanbouw / coming soon" placeholder).
5. On a phone width the layout switches to mobile top bar + bottom tabs.
6. Logout returns to the login screen.

---

## Phase 1 — Content views (read-only)

Build the remaining screens, all reading from the data models (sample fallback).

- Opdrachten (Assignments) — card grid.
- Trainingen — card grid.
- Documenten & bronnen — list with category filters.
- Mededelingen (Announcements) — list.
- Leden (Members) — team grid.
- Mijn profiel — profile view.
- Contact — info + message form (display only this phase).

**✅ Check at end of Phase 1**
1. Every menu item shows its screen, pixel-matching the design.
2. Document category filters switch the visible list.
3. Each screen is correct on desktop and mobile.

---

## Phase 2 — Admin content management

Let SETG manage everything from wp-admin without touching code.

- Meta boxes for every field (assignment, training, document, announcement).
- Document upload (file) or external link + category picker.
- Helpful admin list columns (date, client, category…).
- Member-profile fields (background, expertise, availability, contact pref.).

**✅ Check at end of Phase 2**
1. Creating an assignment/training/document/announcement in wp-admin makes it
   appear in the portal, replacing the sample content.
2. Uploading a document file makes it downloadable in the portal.
3. Editing a member profile updates the Leden + profile screens.

---

## Phase 3 — Interaction & membership

- "Ik ben geïnteresseerd" on assignments (saved; admin sees who responded).
- "Inschrijven" on trainings.
- Simple comments / questions on posts.
- Member registration + **admin approval** flow (approve/revoke access).
- Front-end profile editing by the member.

**✅ Check at end of Phase 3**
1. Clicking "interested"/"register" records the response (visible to admin).
2. A new member stays "pending" until an admin approves, then gains access.
3. A member can edit their own profile.

---

## Phase 4 — Notifications, contact & polish

- Email to all members when a new assignment/training is published.
- Email to SETG on new interest/registration and on contact-form submits.
- Working contact form.
- Empty states, accessibility, final responsive QA, security pass
  (nonces, escaping, capability checks), performance.

**✅ Check at end of Phase 4**
1. Publishing an assignment emails the members.
2. Submitting the contact form reaches SETG.
3. Security/QA review notes addressed.

---

## Phase 5 — Future expansion  *(optional, from the Recommendations PDF)*

Scoped later, one at a time:

- Advanced availability module (per month, region, online/in-person, max hours).
- Competency & certification roles.
- Assignment matching (suggest members for an opportunity).
- Time registration + activity tracking + invoicing.
- Gambling-harm knowledge base.

---

### Working agreement
Finish one phase → owner runs that phase's checklist → confirm → next phase.
