# Warden Module Viva Notes

This file is for explaining the Warden module during viva. The UI and functionality are kept the same; these notes only describe how the module works.

## Main File Map

- `index.php`
  - Main router.
  - Warden routes start with `warden_`, for example `warden_students`, `warden_food`, and `warden_update_food`.

- `views/dashboard/wardenDashboard.php`
  - Warden home page.
  - Shows cards for Students, Food, Laundry, Bathroom Cleaning, Timing, Notices, and Complaints.

- `views/dashboard/warden_students.php`
  - Shows student records.
  - Search works using `data-search` on each row and JavaScript in `public/js/script.js`.

- `views/dashboard/food.php`
  - Shows food status for each student.
  - Dropdown sends Yes/No status to `warden_update_food`.

- `views/dashboard/laundry.php`
  - Shows laundry status for each student.
  - Dropdown sends Yes/No status to `warden_update_laundry`.

- `views/dashboard/cleaning.php`
  - Shows bathroom cleaning status room-wise.
  - Dropdown sends Done/Pending status to `warden_update_cleaning`.

- `views/dashboard/timing.php`
  - Shows check-in and check-out time fields.
  - Time fields auto-save through `warden_update_timing`.

- `views/dashboard/warden_notices.php`
  - Shows notices for the warden.

- `controllers/WardenController.php`
  - Middle layer between view and model.
  - Converts form/AJAX values into values the database understands.

- `models/Warden.php`
  - Database layer.
  - Contains SQL queries for fetching students, food, laundry, cleaning, and timing data.

- `public/js/script.js`
  - Handles Warden dropdown updates, timing auto-save, live search, toast messages, and row detail popup.

- `public/css/warden.css`
  - Warden module styling.
  - Contains sidebar, table, search bar, dropdown, dashboard card, and responsive CSS.

## Simple Flow To Explain

1. User opens a Warden page.
2. `index.php` checks the action, for example `warden_food`.
3. The matching PHP view file is included.
4. The view calls `WardenController`.
5. `WardenController` calls `Warden` model.
6. `Warden` model fetches data from the database.
7. The view displays the data.
8. If the user changes a dropdown, `public/js/script.js` sends an AJAX request back to `index.php`.
9. `index.php` sends that request to `WardenController`.
10. The model updates the database and JavaScript shows a toast message.

## Search Functionality

Search is client-side.

- Each searchable row has class `searchable-row`.
- Important searchable words are stored in `data-search`.
- `public/js/script.js` listens to the search input.
- Rows that do not match are hidden.

If a teacher asks to make search include another field:

1. Open the view file.
2. Find `data-search`.
3. Add that field to the string.

Example:

```php
data-search="<?= htmlspecialchars(strtolower(($s['name'] ?? '') . ' ' . ($s['email'] ?? ''))) ?>"
```

## Status Dropdowns

Food and Laundry use:

- `Yes` means status value `1`
- `No` means status value `0`

Bathroom Cleaning uses:

- `Done` means status value `1`
- `Pending` means status value `0`

JavaScript sends only `id` and `status`. The controller converts the value:

- Food: `1` or `0`
- Laundry: `Completed` or `Pending`
- Cleaning: `Done` or `Pending`

## Timing Auto-Save

Timing fields are saved when the input changes.

- `time-in` field sends `check_in`
- `time-out` field sends `check_out`
- Controller converts blank values to `NULL`
- Model stores the latest timing record

## CSS Changes Teachers May Ask

Change search bar:

- File: `public/css/warden.css`
- Look for `.wd-toolbar > .wd-search`

Change dropdown colors:

- File: `public/css/warden.css`
- Look for `.wd-status-select.yes` and `.wd-status-select.no`

Change sidebar color:

- File: `public/css/warden.css`
- Look for `--wd-navy` inside `:root`

Change card color:

- File: `public/css/warden.css`
- Look for `--wd-card`

## Common Viva Questions

Q: Why did you use MVC style?

A: The view displays HTML, the controller handles requests, and the model handles database queries. This makes the code easier to understand and maintain.

Q: How does live search work?

A: JavaScript reads the search box, compares it with each row's text and `data-search`, then hides rows that do not match.

Q: How does status update without page reload?

A: JavaScript uses `fetch()` to send AJAX data to PHP. PHP updates the database and returns JSON. Then JavaScript shows a success or error message.

Q: Why use prepared statements?

A: Prepared statements protect database queries from SQL injection and safely bind user values.

Q: What is the purpose of `latestRecordId()`?

A: It finds the most recent status row for a student or room. The module updates that row if it exists, otherwise it inserts a new row.

Q: If asked to add a new Warden page, what would you do?

A: Add a route in `index.php`, create a view file in `views/dashboard`, add a controller method if needed, add a model query if database data is needed, and add the menu item to the Warden page map.

Q: If asked to change only UI, where would you edit?

A: Mostly `public/css/warden.css`. If the HTML structure changes, edit the related view file.

## Short Explanation To Say In Viva

"The Warden module follows a simple flow. The route is handled in `index.php`, the page view is loaded from `views/dashboard`, the controller prepares the request, and the `Warden` model handles database work. Status changes and timing updates use JavaScript fetch requests, so the page does not need to reload. Search is handled on the frontend by checking each row's searchable text."
