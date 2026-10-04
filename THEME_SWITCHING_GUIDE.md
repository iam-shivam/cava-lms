# CAVA Studio v2: Theme Switching and Rollback Guide

This document is the source of truth for changing the presentation layer across the public site, authentication pages, student area, course player, payments, document viewer, admin portal, and standalone admin login.

The application remains Core PHP, MySQL, and Bootstrap. A theme change must not alter routes, form actions, field names, CSRF fields, access checks, payment verification, enrollment logic, progress tracking, or JavaScript hooks.

## Presentation architecture

The shared styles load in this order:

```text
Bootstrap 5
  -> assets/css/style.css                  (legacy behavior-compatible styles)
  -> assets/css/landing-premium.css        (legacy public styles)
  -> assets/css/cava-studio-tokens.css     (theme values)
  -> assets/css/cava-studio-components.css (shared UI components)
  -> area stylesheet                       (public, student, or admin)
```

Area stylesheets:

| Area | Stylesheet | Loaded by |
|---|---|---|
| Public pages and authentication | `assets/css/cava-studio-public.css` | `views/layout/header.php` |
| Student dashboard, player, and payment | `assets/css/cava-studio-student.css` | `views/layout/header.php` on student routes |
| Standalone secure document viewer | `assets/css/cava-studio-document-viewer.css` | `document_viewer.php` |
| Admin portal and admin login | `assets/css/cava-studio-admin.css` | `admin/admin_header.php` and `admin/login.php` |

The legacy styles remain loaded as a compatibility and rollback layer. CAVA Studio files are intentionally loaded after them.

## Change the global theme

For a normal brand/theme switch, edit only:

```text
assets/css/cava-studio-tokens.css
```

The `:root` block contains the global palette, type, spacing, radius, shadow, transition, and Bootstrap/legacy token mappings. Prefer changing semantic values such as:

```css
:root {
    --studio-green-950: #103428;
    --studio-green-800: #185b47;
    --studio-lime-400: #b9ea5f;
    --studio-canvas: #f5f2e9;
    --studio-surface: #fffdf8;
    --studio-ink: #15231d;
    --studio-muted: #6f7d76;
    --studio-border: #d8d5ca;

    --studio-font-display: "Manrope", sans-serif;
    --studio-font-body: "DM Sans", sans-serif;
}
```

Do not scatter new brand hex values through PHP templates. Add or update a semantic token, then consume it from a component or area stylesheet.

## Change fonts

1. Update the Google Fonts link in all three entry points:

   - `views/layout/header.php`
   - `admin/admin_header.php`
   - `admin/login.php`

2. Update `--studio-font-display` and `--studio-font-body` in `cava-studio-tokens.css`.
3. Test loading failures; the token stack must retain a system-font fallback.

## Change components or layouts

Use the narrowest appropriate layer:

- Shared buttons, inputs, tables, alerts, cards, focus styles, and reduced-motion behavior: `cava-studio-components.css`.
- Public navigation, footer, home, catalog, course cards, webinar cards, page heroes, and authentication layouts: `cava-studio-public.css`.
- Dashboard, course player, payment, and student-only responsive rules: `cava-studio-student.css`.
- Standalone secure document viewer controls and responsive layout: `cava-studio-document-viewer.css`.
- Admin shell, sidebar, tables, forms, modals, and standalone admin login: `cava-studio-admin.css`.

Keep selectors scoped under `body.cava-studio`, `body.cava-studio-admin`, or `body.cava-studio-auth` when a rule could affect third-party or legacy markup.

## Logo and social assets

The shared brand image is:

```text
assets/images/logo.jpeg
```

The default social image is:

```text
assets/images/og-default.png
```

Keep filenames stable when replacing assets, or update every public/admin entry point together. Optimize images before production deployment.

## Motion

`assets/js/cava-studio.js` provides shared, non-critical presentation behavior. `assets/js/cava-studio-home.js` adds selective homepage GSAP motion only when GSAP is available and motion is allowed.

- Functional application behavior remains in `assets/js/main.js` and page scripts.
- `prefers-reduced-motion: reduce` disables non-essential motion and reveals content statically.
- Pages remain readable if JavaScript or the GSAP CDN is unavailable.
- Three.js is not loaded by v2. If a future homepage enhancement uses it, load it only on the homepage as a separate optional asset and provide a static CSS/image fallback.

## Cache invalidation

After a production-approved theme change, increment the `?v=` value for the changed CAVA Studio assets in the relevant shared headers or footer. Deploy the updated asset and its reference in the same release.

## Required visual regression checks

Test at roughly 375px, 768px, 1024px, and 1440px, including keyboard-only use and 200% zoom.

- Public: home, course catalog/detail, webinars, events, support, privacy, and 404.
- Authentication: login, registration, OTP entry, validation, and error messages.
- Student: dashboard tabs/forms, course player sidebar, locked/unlocked lessons, video OTP, progress, documents, and payment outcomes.
- Admin: login, responsive sidebar, dashboard, all tables/forms/uploads, filters, pagination, dropdowns, and confirmation modal.
- Motion: normal mode, reduced-motion mode, GSAP blocked, and JavaScript disabled.

Always verify contrast, visible focus, error/success states, touch targets, long text, empty states, and real database content.

## Atomic rollback

Do not roll back only the CSS. The v2 templates, styles, and scripts form one presentation release and must be restored together.

Treat the redesign as one coordinated presentation commit. Do **not** use reset, clean, or partial file checkout as a rollback shortcut when other local work is present. Preserve any later local changes first, then revert the coordinated redesign commit from a known revision.

If reverting v2 inside another branch, restore these groups in one change:

1. Shared/public templates and page markup: `views/layout/`, `views/components/`, and the modified root PHP pages.
2. Admin templates: `admin/admin_header.php`, `admin/admin_footer.php`, and `admin/login.php`.
3. Scripts: `assets/js/main.js`, `assets/js/cava-studio.js`, and `assets/js/cava-studio-home.js`.
4. Styles: all `assets/css/cava-studio-*.css` files.

The legacy `assets/css/style.css` and `assets/css/landing-premium.css` files were retained, which makes a coordinated rollback possible. Removing only the new stylesheet links is not a complete rollback because v2 also contains new semantic markup.

Before any rollback or theme release:

1. Preserve the current working tree, including untracked CAVA Studio assets, in an explicitly authorized recoverable snapshot.
2. Use a staging environment with a representative database.
3. Run PHP lint and JavaScript syntax checks.
4. Exercise authentication, enrollment, player, document, and payment paths.
5. Deploy only with explicit approval; no database or secret changes are required for the theme itself.

## CAVA Studio v2 implementation record

Branch: `feature/cava-lms-redesign-v2`

New presentation assets:

```text
assets/css/cava-studio-tokens.css
assets/css/cava-studio-components.css
assets/css/cava-studio-public.css
assets/css/cava-studio-student.css
assets/css/cava-studio-admin.css
assets/css/cava-studio-document-viewer.css
assets/js/cava-studio.js
assets/js/cava-studio-home.js
```

Updated presentation entry points and components:

```text
views/layout/header.php
views/layout/footer.php
views/components/course_card.php
views/components/webinar_card.php
index.php
courses.php
course.php
webinars.php
events.php
support.php
privacy-policy.php
404.php
login.php
register.php
otp_verify.php
dashboard.php
course_play.php
payment_process.php
document_viewer.php
admin/admin_header.php
admin/admin_footer.php
admin/login.php
admin/dashboard.php
admin/courses.php
admin/videos.php
admin/section_edit.php
admin/video_edit.php
admin/webinars.php
admin/events.php
admin/payments.php
admin/email_templates.php
admin/email_template_add.php
admin/email_template_edit.php
admin/settings.php
assets/js/main.js
```

The shared admin shell and stylesheet also cover `admin/categories.php`, `admin/users.php`, `admin/enrollments.php`, `admin/queries.php`, and the export route. Those pages were individually reviewed even though their page files did not require presentation edits.

Reference boundaries: TagMango and Graphy informed public-page hierarchy, Maven informed course-discovery clarity, and Thinkific informed player organization. No reference branding, copy, claims, or unsupported features were added.

### Validation performed on 4 October 2026

- PHP syntax: all 76 application PHP files passed `php -l`.
- JavaScript syntax: all 5 files in `assets/js` passed `node --check` with Node v22.14.0.
- CSS structure: all 6 CAVA Studio stylesheets had balanced braces.
- Git whitespace validation: `git diff --check` passed; the repository emitted only existing LF-to-CRLF normalization warnings.
- Contract audit: preserved reviewed form actions, field names, CSRF inputs, navigation IDs, catalog hooks, dashboard profile hooks, Razorpay data attributes, course-player controls, document-viewer controls, and admin confirmation/password hooks.
- Database-backed runtime checks used a separate loopback-only MariaDB instance and clearly identified synthetic records. The existing XAMPP database and its data directory were not started, repaired, reset, or overwritten.
- Runtime flow log: 40 primary HTTP checks produced 39 passes and 1 failure. The failure is the unreachable existing partial-payment preview path. A supplemental check also found that video OTP reports success after mail delivery fails.
- Route coverage: all actual root/API/admin PHP entry points were inventoried; public routes and every authenticated admin presentation route were checked individually. Admin category creation and missing-CSRF rejection were executed safely.
- Browser QA: the homepage, catalog, course detail, dashboard, locked player, login, and admin dashboard were captured in headless Chrome at 390, 768, 1024, and 1440 pixels. Representative admin, long-text, missing-image, empty, and error states were also captured.
- Non-Chrome product sample: headless Microsoft Edge covered the homepage, catalog, dashboard, support, and admin payments. Edge remains Chromium-engine coverage, not an independent engine.
- Accessibility/motion: sampled keyboard focus, reduced motion, and GSAP-unavailable static behavior passed. CDP page-scale testing was performed but is not treated as native 200% browser-zoom approval.
- Visual metrics: 42 final metric records reported no document-level horizontal overflow; the primary matrix reported no broken images.
- Detailed validation evidence and security findings are retained outside public source control for the project owner.

No unit or integration test suite is declared in `composer.json`; validation therefore used syntax, contract, route, HTTP, and browser checks.

### Still unverified

- Successful registration and delivered login/video OTP through an approved SMTP/SMS test service.
- Successful video playback through an approved video service. Playback without video OTP was correctly denied.
- Razorpay Test Mode success, failure, cancellation, partial-payment callback, and repeated-callback/idempotency behavior.
- Google OAuth, Google Sheets/webhook delivery, Bunny Stream, and upload mutations.
- Admin mutations other than category creation, including destructive confirmation and upload outcomes.
- Complete keyboard traversal, assistive-technology testing, and native browser 200% zoom.
- Firefox and Safari. Edge was tested, but it uses Chromium.
- Recovery of the existing XAMPP MariaDB instance, whose log contains an InnoDB assertion/corruption warning.

The branch must not be called production-ready while the critical payment/video/access findings and these blocked paths remain. No production rows, secrets, production configuration, or deployment state were changed.
