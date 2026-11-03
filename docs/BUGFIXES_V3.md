# My Daily OS v3 — Bugs Found & Fixed, UX Improvements

## Confirmed bugs (root cause → fix)

1. **Repeat-click delete → error page (user-reported, root cause confirmed).** First click soft-deleted the record; a 2nd/3rd rapid click re-submitted DELETE and route-model binding could not find the trashed model → 404 error page. FIX: all 15 delete endpoints (tasks, routine, projects, habits, learning goals+sessions, meals, finance, income, office, sleep, water, weight, walking, exercise) now resolve records manually (withTrashed where applicable); a repeat delete redirects with "Already deleted" — never an error page. The UI also disables buttons on first submit and Delete uses a two-step "Confirm delete?" state.
2. **Theme settings did nothing (user-reported).** Theme/accent were saved to the database but no layout or CSS ever read them; no light theme existed. FIX: layouts emit data-theme/data-theme-resolved + accent from the user's settings; a full light palette + system (OS-following) mode implemented; accent color applies app-wide; charts follow the theme.
3. **Freelancing Projects page error (found in audit).** The index sorted by a `date` column that does not exist on the projects table → SQL error every visit. FIX: sort by created_at.
4. **Double-click Complete created duplicate completion records.** The completion insert was keyed with the current timestamp. FIX: keyed on (user, task); repeats are no-ops.
5. **Habit streak ignored today.** Habit log dates stored with a time suffix were excluded by date-string range queries (streak showed 2 instead of 3). FIX: date-safe queries.
6. **Several deletes returned silently** (no confirmation message). FIX: every delete redirects to its module list with a clear success message.
7. **Settings form gaps.** Working days, weekly exercise target, quiet hours and date format were accepted by the backend but had no inputs, so office working days could never be changed. FIX: inputs added. Time-format and notifications selects also failed to pre-select saved values — fixed.
8. **Reports layout on laptop (user-reported).** Charts were bare canvases with fixed pixel heights inside an unstructured layout → clipping/overflow. FIX: rebuilt with a proper grid, aspect-ratio chart boxes, bottom legends, KPI cards, tables in responsive wrappers below charts.
9. **Habits page (user-reported).** Only today's data was even supplied to the page, so consistency could not be shown. FIX: backend now supplies streak, week progress and 84-day history; page rebuilt around them.

## Meaningful UX improvements
- Dashboard is now a command center: greeting + live clock, Next Up, a chronological **Today's Timeline** (tasks, five prayers with quick status, office block incl. cross-midnight, meals, wake-up), progress cards, theme-aware charts, one-tap quick actions.
- **Routine Builder** (/routine): build the repeating plan once (workday vs day-off groups, days in words); My Day shows today's copy; editing a routine changes future days only — history is never rewritten.
- **My Day** redesigned: date navigation, Timed vs Anytime groups, times in words, "Move to tomorrow" instead of "Postpone".
- Task form explains Planned start/end in plain words and distinguishes planned time from a deadline; recurrence options in human words with weekday checkboxes.
- **Habits**: Done-today toggle, streak, Mon–Sun week dots, 12-week consistency heatmap, example habits that prefill (never fake records), no pressure language.
- **Reports**: "What this means" insights computed only from real records (e.g. tasks completed x of y; walking change vs previous period) + honest insufficient-data notes.
- **Onboarding** (/welcome): 8-step setup checklist computed from real data, dismissible, reopenable from Help; dashboard shows it until done/dismissed.
- Delete safety everywhere: two-step confirm + buttons disable on submit.
- Beginner documentation: docs/USER_GUIDE_ROMAN_URDU.md (full daily workflow in simple Roman Urdu) + in-app Help updates.

## What was actually tested (v3)
- PHPUnit: **55 passed / 344 assertions** — includes full create→edit→action→delete workflows for every major module, double/triple-delete idempotency, double-complete, routine edit-preserves-history, habit streak math, report insights with/without data, onboarding step flips, dashboard timeline contents, cross-user isolation.
- Real HTTP flows against a running server (fresh DB): register → dashboard (timeline, Next Up, onboarding card present) → create timed task → delete → **delete again → 302 with "Already deleted", no error page** → save Settings theme=light → dashboard renders data-theme="light" → routine/welcome/habits/reports/salah all 200 with new markup.
- Theme also verified by the design pass over real HTTP (light + custom accent confirmed in rendered pages).
- **Not verified in this environment:** pixel-level screenshots in a real browser (the available browser runs in a separate VM that cannot reach the local server, and outbound tunnels are blocked) and physical-device checks at the six named viewport sizes — responsiveness is implemented via documented CSS breakpoints (992px/768px) and verified at markup/CSS level + full HTTP renders, not via device screenshots. iPhone push behaviour remains as documented in docs/NOTIFICATIONS.md.

## v3.1 hotfix (2026-11-03 night) — unreadable dark text on dashboard (user-reported with screenshot)
ROOT CAUSE (reproduced locally in a real headless Chrome WITH Bootstrap loaded): Bootstrap 5.3.3's `.card{color:var(--bs-body-color)}` paints all card text #212529; v3's app.css set the card background but never a text color, so every heading/title/plain line inside a card rendered near-black on the dark card. Elements with explicit app.css colors (muted text, pills, labels) stayed visible — exactly the pattern in the user's screenshot. My earlier "theme verified" checks inspected HTML attributes, not rendered colors; the user's screenshot caught what they could not.
FIX (public/css/app.css only, + cache-busting `?v=3.1` on css/js links in both layouts): `.card{color:var(--text)}`, card headings pinned to var(--text), explicit colors on chips/ring/timeline-body/theme-options, and both theme blocks now set Bootstrap's own variables (--bs-body-color, --bs-heading-color, --bs-secondary-color, --bs-link-color, --bs-border-color, --bs-card-color, --bs-progress-bg/bar) from the theme variables so Bootstrap defaults can never leak through anywhere.
ALSO FIXED (spotted in the same screenshot QA): the timeline labelled EVERY past unmarked prayer "Now". Now only the current waqt is 'active'; earlier unmarked prayers show "Past" (never auto-'missed'). Regression test: DashboardTimelineTest::test_only_current_prayer_is_active_at_night.
VERIFIED VISUALLY this time: headless-Chrome screenshots of the dashboard with Bootstrap actually loaded — dark theme, light theme, and 390px mobile — all text readable; computed styles: .card/.card h5/.tl-title/.stat all rgb(243,245,243) in dark, rgb(20,32,26) in light. Suite: 56 passed / 351 assertions.
