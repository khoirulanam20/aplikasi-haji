# UI/UX Design System — Starter Kit L13

> **Theme:** Light Mode · Clean Minimal · Apple-inspired Continuous Curves  
> **Base framework:** Laravel 13 + Tailwind CSS 3 + Alpine.js 3 + Vite 8  
> **Icon library:** [Iconoir](https://iconoir.com) (CDN)  
> **Primary font:** [Inter](https://fonts.google.com/specimen/Inter) (400/500/600/700)

---

## 1. Color System

### 1.1 Core Palette

| Token | HEX | Usage |
|-------|-----|-------|
| `primary` | `var(--color-primary)` ~ `#FF5B37` (Coral/Orange) | Buttons, active indicators, chart highlights, avatars |
| `primary-hover` | `var(--color-primary-hover)` ~ `#E55231` | Button hover state |
| `accent` | `var(--color-secondary)` ~ `#4B5694` (Indigo) | Sidebar active background, badges |
| `accent-hover` | `var(--color-secondary-hover)` ~ `#3D4678` | Accent hover state |
| `background` | `#F7F8FA` (Light Gray) | Main app body background |
| `surface` | `#FFFFFF` (White) | Cards, dropdowns, modals, sidebar |
| `surface-dark` | `#1A1D1F` (Dark Charcoal) | Dark cards (e.g., virtual card), dark buttons |
| `text-primary` | `#1A1D1F` (Near Black) | Headings, titles, primary text |
| `text-secondary` | `#6F767E` (Gray) | Labels, subtitles, secondary text, placeholders |
| `border` | `#EFEFEF` (Very Light Gray) | Dividers, borders, hairline rules |
| `success` | `#10B981` (Green) | Positive badges, "Active" status, percentage up |
| `danger` | `#EF4444` (Red) | Destructive actions, error states, percentage down |
| `warning` | `#F59E0B` (Yellow) | Pending status, impersonation banner |

### 1.2 Dynamic Theming (CSS Custom Properties)

Colors are injected at runtime via `theme-head.blade.php`:

```css
:root {
  --color-primary: #FF5B37;
  --color-primary-hover: #E55231;
  --color-secondary: #4B5694;
  --color-secondary-hover: #3D4678;
}
```

The Alpine `themeState` component (`app.js`) supports switching between multiple color schemes saved to `localStorage`:

```js
setColors(color) {
  root.style.setProperty('--color-primary', `var(--color-${color})`);
  root.style.setProperty('--color-primary-50`, `var(--color-${color}-50)`);
  root.style.setProperty('--color-primary-light`, `var(--color-${color}-light)`);
  root.style.setProperty('--color-primary-dark`, `var(--color-${color}-dark)`);
}
```

### 1.3 Badge Color Variants

| Badge | Background | Text |
|-------|-----------|------|
| `.badge` (default) | `bg-gray-100` | `text-text-secondary` |
| `.badge-success` | `bg-green-50` | `text-success` (#10B981) |
| `.badge-warning` | `bg-yellow-50` | `text-warning` (#F59E0B) |
| `.badge-danger` | `bg-red-50` | `text-danger` (#EF4444) |

---

## 2. Typography

### 2.1 Font Stack

```js
fontFamily: { sans: ['Inter', ...defaultTheme.fontFamily.sans] }
```

Loaded via Google Fonts: `Inter` weights 400 (Regular), 500 (Medium), 600 (Semibold), 700 (Bold).

The welcome page additionally loads **Cairo** (200–900) for the marketing/landing page.

### 2.2 Type Scale

| Level | Size | Weight | Line Height | Usage |
|-------|------|--------|-------------|-------|
| Page Title | 32px | Bold (700) | 40px | `.page-title` — main page headings |
| Card Title | 24px | Semi-Bold (600) | 32px | `.card-title` — card headers |
| H3 | 18px | Semi-Bold (600) | 28px | Small statistics |
| Body Main | 14px | Regular/Medium (400/500) | 20px | Body text, table cells, dropdown items |
| Body Small | 12px | Regular (400) | 16px | Labels, metadata, percentages, dates |
| Table Header | 12px | Medium (500) | — | `.table-header` |
| Sidebar Label | 14px | Medium (500) | — | `.sidebar-link` |
| Section Group | 12px | Semi-Bold (600) | — | Sidebar group titles (uppercase, tracking-wide) |

### 2.3 Base Classes

```css
body { @apply bg-background text-text-primary font-sans antialiased; }
h1, h2, h3, h4, h5, h6 { @apply text-text-primary font-semibold; }
.page-title  { @apply text-[32px] font-bold leading-[40px] text-text-primary; }
.card-title  { @apply text-[24px] font-semibold leading-[32px] text-text-primary; }
```

---

## 3. Border Radius Scale (Apple Continuous Curves)

| Token | Value | Usage |
|-------|-------|-------|
| `rounded-sm` | **8px** | Badges, tags, checkboxes, small buttons (`btn-sm`) |
| `rounded-md` | **12px** | Inputs, buttons, dropdown menus, icon buttons |
| `rounded-lg` | **24px** | Cards, modals, medium containers |
| `rounded-xl` | **32px** | Large containers, big feature cards |
| `rounded-full` | **9999px** | Pill buttons, avatars, circular icon buttons |

**Design principle:** Inner radius ≈ Outer radius − Padding. In practice, the project uses independent radii per component (e.g., button inside a 24px card → 12px).

---

## 4. Shadows & Elevation

| Token | Value | Usage |
|-------|-------|-------|
| `shadow-card` | `0 4px 20px rgba(0,0,0, 0.03)` | Cards — extremely subtle |
| `shadow-xl + ring-1 ring-black/5` | — | Modals |
| `shadow-lg + border border-border` | — | Dropdowns |
| `shadow-xl` | — | Mobile menu drawer |

**Design philosophy:** Avoid heavy shadows. Prefer contrast against the `#F7F8FA` light gray background.

---

## 5. Spacing & Layout

### 5.1 Layout Structure

```
+---------------------------------------------------+
| [Sidebar (256px / 80px)]  [Top Nav (80px)]        |
|                           [Main Content]           |
|                                                    |
|   +-------+  +-------+  +-------+                 |
|   | Card  |  | Card  |  | Card  |                 |
|   +-------+  +-------+  +-------+                 |
+---------------------------------------------------+
```

### 5.2 Key Dimensions

| Element | Size |
|---------|------|
| Sidebar (expanded) | `w-64` (256px) |
| Sidebar (collapsed) | `w-20` (80px) — icons only |
| Top nav height | `h-20` (80px) |
| Card default padding | `p-6` (24px) |
| Small card padding | `p-4` or `p-5` (16–20px) |
| Page content padding | `p-4 sm:p-6 lg:p-8` |
| Guest layout max width | `max-w-md` (448px) |
| Content max width | Fluid (max-w-7xl ~1280px) |

### 5.3 Spacing Scale (8pt Grid)

- Grid gap between cards: `gap-4` (16px) or `gap-6` (24px)
- Space between sections: `space-y-6` (24px)
- Heading to content: `mb-6` (24px)
- Sidebar group separation: `mt-4` (16px)
- Sidebar item padding: `px-3 py-2.5`
- Table cell padding: `px-4 py-4`
- Input padding: `px-4 py-2`

### 5.4 Responsive Breakpoints

| Breakpoint | Width | Behavior |
|------------|-------|----------|
| `<768px` | Mobile | Sidebar hidden, hamburger menu, slide-in drawer, single-column grids |
| `md:` (768px) | Tablet+ | Sidebar visible, 2+ column grids |
| `lg:` (1024px) | Desktop | Full layout, expanded containers |
| `xl:` (1280px) | Wide | Max-width capped |

Common responsive patterns:
- `grid sm:grid-cols-2 lg:grid-cols-3` — stat cards
- `flex-col sm:flex-row sm:items-center sm:justify-between` — header rows
- `hidden md:flex` — sidebar visibility toggle
- `hidden sm:block` — desktop-only page title

---

## 6. Components

### 6.1 Buttons

**Base class:** `.btn`
```css
.btn {
  @apply inline-flex items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-medium
         transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary
         focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50;
}
```

| Variant | Class | Background | Text | Border | Hover |
|---------|-------|-----------|------|--------|-------|
| Primary | `btn-primary` | `bg-primary` (#FF5B37) | white | — | `bg-primary-hover` |
| Secondary | `btn-secondary` | `bg-white` | `text-text-primary` | `border border-border` | `bg-gray-50` |
| Dark | `btn-dark` | `bg-surface-dark` (#1A1D1F) | white | — | `bg-black` |
| Danger | `btn-danger` | `bg-danger` (#EF4444) | white | — | `hover:bg-red-600` |

| Size | Class | Padding | Text | Radius |
|------|-------|---------|------|--------|
| Small | `btn-sm` | `px-3 py-1.5` | `text-xs` | `rounded-sm` (8px) |
| Default | — | `px-4 py-2` | `text-sm` | `rounded-md` (12px) |
| Large | `btn-lg` | `px-6 py-3` | `text-base` | `rounded-lg` (24px) |

**Icon-only buttons:**

| Variant | Class | Style |
|---------|-------|-------|
| Secondary | `btn-icon-secondary` | `border-border bg-white text-text-secondary hover:bg-gray-50 hover:text-text-primary` |
| Danger | `btn-icon-danger` | `border-red-200 bg-white text-danger hover:border-danger hover:bg-red-50` |

Blade components: `<x-primary-button>`, `<x-secondary-button>`, `<x-danger-button>`, `<x-icon-action-button>`

### 6.2 Cards

```css
.card {
  @apply rounded-lg bg-surface shadow-card p-6;
}
```
- **Background:** White (`#FFFFFF`)
- **Radius:** 24px (`rounded-lg`)
- **Shadow:** Ultra-subtle (`0 4px 20px rgba(0,0,0, 0.03)`)
- **Padding:** 24px (`p-6`)

Used for: dashboard stat cards, form containers, modal content, settings panels.

### 6.3 Inputs & Forms

```css
.input {
  @apply block w-full rounded-md border border-border bg-gray-50 px-4 py-2 text-sm text-text-primary
         placeholder-text-secondary transition-colors duration-200
         focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary;
}
```
- **Default:** `bg-gray-50`, `border-border` (#EFEFEF), 12px radius
- **Focus:** White background, primary-color border + ring
- **Error:** `border-danger ring-1 ring-danger` (`.input-error`)
- **Label:** `.label` — `mb-1.5 block text-sm font-medium text-text-secondary`
- **Error text:** `.error-text` — `mt-1 text-xs text-danger`

Blade components: `<x-text-input>`, `<x-input-label>`, `<x-input-error>`

**Form layout pattern:**
```blade
<form method="POST" action="..." class="space-y-6">
  <div>
    <x-input-label for="field" :value="__('Label')" />
    <x-text-input id="field" name="field" class="mt-1 block w-full" />
    <x-input-error :messages="$errors->get('field')" class="mt-2" />
  </div>
  <div class="flex items-center gap-4">
    <x-primary-button>{{ __('Save') }}</x-primary-button>
  </div>
</form>
```

**Form variants:**
- **Inline forms:** `flex gap-2` for search + button combos
- **Grid forms:** `grid gap-6 md:grid-cols-2` for multi-column
- **File inputs:** Styled via `file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2`

### 6.4 Badges (Pills)

```css
.badge {
  @apply inline-flex items-center justify-center rounded-full px-2.5 py-0.5 text-xs font-medium gap-1.5;
}
```
- Pill-shaped (`rounded-full`)
- Small text (`text-xs`)
- Used for status indicators, labels, tags

Blade: used inline with CSS classes (no dedicated component).

### 6.5 Tables

```css
.table-container { @apply w-full overflow-x-auto; }
.table           { @apply w-full text-left border-collapse; }
.table-header    { @apply border-b border-border px-4 py-3 text-xs font-medium text-text-secondary; }
.table-cell      { @apply border-b border-border px-4 py-4 text-sm text-text-primary; }
```

**Pattern:** Bottom borders only, `bg-gray-50` header background, `hover:bg-gray-50` row hover, action icons in last column.

### 6.6 Modals

**Generic modal (`<x-modal>`):**
- Backdrop: `bg-gray-900/60 backdrop-blur-[2px]`
- Content wrapper: `card shadow-xl ring-1 ring-black/5`
- Animation: scale-95 → scale-100 + fade + translate-y
- Sizes: `sm` (384px), `md` (448px), `lg` (512px), `xl` (576px), `2xl` (672px default)
- Teleported to `<body>` via `<template x-teleport="body">`
- Close on ESC, click backdrop to close

**Alert/Confirm modal (`<x-alert-modal>`):**
- Powered by `$store.alert` (Alpine store in `alert-modal.js`)
- Modes: `alert` (single OK) / `confirm` (Cancel + Confirm)
- Types with color-coded icons:
  - `info` — blue circle
  - `success` — green check
  - `error` — red xmark
  - `warning` — amber warning
  - `danger` — red warning
- Danger confirm button auto-styles to `bg-danger`
- ESC to dismiss
- Backdrop click: alert mode → accept, confirm mode → dismiss

**Confirm form wrapper (`<x-confirm-form>`):**
- Intercepts form submit → opens confirm modal → submits only if confirmed

### 6.7 Dropdowns

```blade
<div class="relative" x-data="{ open: false }" @click.outside="open = false">
  <div @click="open = !open">{{ $trigger }}</div>
  <div x-show="open"
       x-transition:enter="transition ease-out duration-100"
       x-transition:enter-start="transform opacity-0 scale-95"
       x-transition:enter-end="transform opacity-100 scale-100"
       x-transition:leave="transition ease-in duration-75"
       x-transition:leave-start="transform opacity-100 scale-100"
       x-transition:leave-end="transform opacity-0 scale-95"
       class="absolute right-0 mt-2 w-48 ...">
    {{ $content }}
  </div>
</div>
```

- Animated: scale + fade
- Shadow: `shadow-lg`
- Border: `border border-border`
- Radius: `rounded-xl` (menu container), `rounded-md` (items)
- Click-away close: `@click.outside`

**Pre-built component:** `<x-dropdown>` with align (left/right/top), width, and contentClasses props.

### 6.8 Navigation

**Sidebar (`w-64` / `w-20`):**
- Logo area: app logo/initial + app name + collapse button
- Group titles: uppercase, `text-xs`, `tracking-wide`, `text-text-secondary`
- Menu items: `.sidebar-link` with `gap-3`, `rounded-md`, `px-3 py-2.5`
- Active: `.sidebar-link-active` = `bg-accent text-white` (#4B5694)
- Inactive: `.sidebar-link-inactive` = `text-text-secondary hover:bg-gray-100 hover:text-text-primary`
- Collapsible: animates width, hides labels, centers icons
- Bottom: logout button

**Top navigation (`h-80`):**
- Left: hamburger (mobile), page title (desktop)
- Right: search input (desktop), notification bell with badge, profile dropdown
- Search: expands on focus (`group-focus-within:w-80`)

**Mobile navigation:**
- Slide-in drawer from left: `-translate-x-full` → `translate-x-0`
- Backdrop overlay: `bg-gray-900/50` with fade transition
- Close button with `iconoir-cancel`

### 6.9 User Avatar (`<x-user-avatar>`)

- Renders `<img>` if `$user->avatarUrl()` exists (with `rounded-full object-cover border border-border`)
- Falls back to initial letter in a `bg-primary` circle
- Sizes: `sm` (32px), `md` (40px default), `lg` (56px)

### 6.10 Icon Picker (`<x-icon-picker-modal>`)

- Searchable grid modal for selecting icons from config (`config('iconoir.icons')`)
- Grid: 4 cols mobile, 8 cols desktop
- Search input filters icons by name/label
- Selection dispatches `icon-selected` event

### 6.11 Drag-and-Drop Reordering

**Module/Group ordering (`modules-order.js`):**
- Uses SortableJS with `handle` option (`.group-drag-handle` / `.module-drag-handle`)
- `animation: 150`
- On reorder, sends PUT request with new IDs array
- Error handling via `window.alertModal?.alert()`

---

## 7. Animation & Transitions

| Context | Duration | Easing | Transform |
|---------|----------|--------|-----------|
| Button hover | 200ms | — | Background color |
| Sidebar collapse | 300ms | — | Width transition |
| Modal/dropdown enter | 100–200ms | ease-out | `scale-95 → scale-100` + `opacity-0 → 1` + `translate-y-4 → 0` |
| Modal/dropdown leave | 75–150ms | ease-in | `scale-100 → scale-95` + `opacity-1 → 0` + `translate-y-0 → 4` |
| Overlay fade | 300ms | linear | `opacity-0 → 1` |
| Mobile drawer | 300ms | ease-in-out | `-translate-x-full → translate-x-0` |
| Table row hover | — | — | `bg-gray-50` |
| Card hover | — | — | `hover:border-primary/30` |

---

## 8. Dark Mode

Configured via `darkMode: 'class'` in Tailwind. The `themeState` Alpine component:
- Persisted to `localStorage` as `dark` (boolean)
- Falls back to `prefers-color-scheme: dark`
- Toggles `dark` class on `<html>`

**Current state:** Dark mode styling is partially implemented — mainly present on the welcome/landing page. The admin panel (`app.blade.php`) does not have comprehensive dark mode.

---

## 9. Flash Messages & Alerts

Flash messages from `session('status')` are resolved through `App\Support\FlashMessages` and displayed as alert modals on page load:

```blade
<div x-data x-init="$nextTick(() => $store.alert.alert(@js($flash)))" class="hidden"></div>
```

Validation errors are shown as error alerts automatically. Types: `success`, `error`, `warning`, `info`, `danger`.

Global JS API:
```js
window.alertModal.alert(options)    // Alert mode
window.alertModal.confirm(options)  // Confirm mode (returns Promise<boolean>)
```

---

## 10. Guest Layout (Auth Pages)

- Full-page centered layout with `min-h-screen flex items-center justify-center`
- Logo + app name above the form
- Card container: `max-w-md` with `card` class
- Used for: login, register, forgot password, reset password

---

## 11. Welcome / Landing Page

Standalone page with:
- Different font: **Cairo** (200–900)
- Full-page gradient background with blur orbs (`bg-primary/10 blur-[100px]`)
- Dark mode toggle button
- Hero section with badge, heading, description, and CTA buttons
- Responsive: stacked on mobile, side-by-side on desktop

---

## 12. Icon System

**Library:** [Iconoir](https://iconoir.com) — loaded via CDN.

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css" />
```

Usage: `<i class="iconoir-{name}"></i>`

**Commonly used icons:**

| Category | Icons |
|----------|-------|
| Navigation | `iconoir-search`, `iconoir-bell`, `iconoir-user`, `iconoir-log-out`, `iconoir-menu`, `iconoir-cancel` |
| Actions | `iconoir-edit`, `iconoir-trash`, `iconoir-plus`, `iconoir-eye`, `iconoir-floppy-disk`, `iconoir-lock` |
| Sidebar | `iconoir-dashboard-dots`, `iconoir-database`, `iconoir-shield`, `iconoir-settings`, `iconoir-group`, `iconoir-app-window` |
| Arrows | `iconoir-arrow-left`, `iconoir-nav-arrow-down`, `iconoir-nav-arrow-right`, `iconoir-sidebar-collapse`, `iconoir-sidebar-expand` |
| Status | `iconoir-info-circle`, `iconoir-warning-circle`, `iconoir-warning-triangle`, `iconoir-check-circle`, `iconoir-xmark-circle` |
| Theme | `iconoir-half-moon`, `iconoir-sun-light` |
| Misc | `iconoir-drag-hand-gesture`, `iconoir-flash`, `iconoir-emoji-satisfied` |

---

## 13. Build Tooling

| Tool | Version | Purpose |
|------|---------|---------|
| **Vite** | ^8.0.0 | Build tool |
| **Laravel Vite Plugin** | ^3.1 | Laravel integration |
| **Tailwind CSS** | ^3.1.0 | Utility CSS framework |
| **@tailwindcss/forms** | ^0.5.2 | Form reset styles |
| **PostCSS** | ^8.4.31 | CSS processing |
| **Autoprefixer** | ^10.4.2 | Vendor prefixes |
| **Alpine.js** | ^3.4.2 | Reactive UI (via `alpinejs` npm) |
| **SortableJS** | ^1.15.7 | Drag-and-drop reordering |
| **Iconoir** | CDN | Icon library |

**Vite entry points:**
- `resources/css/app.css` — Tailwind base + component classes
- `resources/js/app.js` — Alpine initialization + theme state
- `resources/js/modules-order.js` — SortableJS drag-and-drop

---

## 14. Blade Component Tree

### Admin Layout (`app.blade.php`)

```
html.h-full.bg-background
  head
    theme-head.blade.php (CSS vars, favicon)
    Iconoir CDN
    @vite(app.css, app.js)
  body.overflow-hidden.h-screen.flex
    aside.w-64/w-20 (sidebar)
      Logo area (logo/initial + name + collapse button)
      nav > sidebar-nav.blade.php
        sidebar-nav-item.blade.php (active/inactive states)
      Logout button
    div.flex-1 (main wrapper)
      header.h-20 (top nav)
        hamburger (mobile) | page title (desktop)
        search (desktop) | notifications dropdown | profile dropdown
      mobile menu overlay + drawer (copies sidebar-nav)
      impersonate banner
      main > flash-alert.blade.php + $slot
      x-alert-modal (teleported to body)
```

### Guest Layout (`guest.blade.php`)

```
html.bg-background
  head (same as app)
  body.min-h-screen.items-center.justify-center
    Logo + app name link
    main.max-w-md > div.card
      flash-alert + $slot
    x-alert-modal
```

---

## 15. Alpine.js Components & Stores

**Global store:** `$store.alert`
- `alert()`, `confirm()`, `accept()`, `dismiss()`
- Reactive: `open`, `mode`, `type`, `title`, `message`, `iconClassName`, `iconWrapClassName`

**Global data:** `themeState`
- `dark`, `color`, `isMobileMainMenuOpen`, `isSettingsPanelOpen`, `isSearchPanelOpen`, `userOpen`
- Methods: `toggleTheme()`, `setColors()`, `applyTheme()`, `openSettingsPanel()`, `openSearchPanel()`

**Per-page data:**
- `collapsed` — sidebar toggle (persisted to localStorage)
- `mobileMenuOpen` — mobile drawer
- `profileOpen`, `notifOpen` — dropdown toggles
- `confirmed` — confirm form guard

---

## 16. Design Principles Summary

1. **Clean & Minimal** — Generous whitespace, subtle shadows, light gray background
2. **Consistent Radius** — Apple-inspired continuous curves (8/12/24/32px)
3. **Icon-First Navigation** — Every nav item, action, and status uses Iconoir
4. **Modal-Centric CRUD** — Create/edit/delete operations via modals, not separate pages
5. **Alpine.js Reactivity** — Dynamic interactions without heavy JS frameworks
6. **RBAC-Protected UI** — All actions gated by `@can` directives
7. **Mobile-Responsive** — Collapsible sidebar, drawer menu, responsive grids
8. **Dynamic Theming** — CSS custom properties enable runtime primary color switching
9. **Form Reuse Pattern** — Consistent label + input + error + action structure
10. **Indonesian-First** — Admin UI labels in Indonesian
