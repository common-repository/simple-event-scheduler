=== Simple Event Scheduler ===
Contributors: andreyvdenisov
Tags: event, schedule, calendar, visit
Requires at least: 4.1
Tested up to: 5.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create and manage events scheduling for registered site users and presenting it as a calendar.

== Description ==

Simple Event Scheduler plugin is designed for managing events scheduling for registered site users and presenting it as a calendar.

The key control of the plugin is Event Calendar.

This plugin gives the next main advantages:
- Shortcode for web control with special parameter for selecting the events owner – current user, current post author, user id or all users
- Customizable date and time format – plugin uses WordPress site settings, admin can change it from standard WordPress settings page
- Timezones for users – all dates and times will be shown according to the selected timezone
- Registered users can mark that they are going to visit event
- Dashboard page: admin can edit users events, set their time zone
- Uploading events for the next dates via Ajax, without page reload
- Reduced Ajax traffic for better performance and faster data upload
- Standard form for creating and editing events with name and duration fields, and captcha for preventing malicious requests

Event Calendar control shows users events as a calendar. Each column of this calendar is a day, events are shown from top to bottom ordered by start time. Ajax is used for uploading the events for next days, so user don't need to wait the page reloading. Being shown for event owner, it allows to create, edit or delete an event. When shown for other users, it gives the opportunity to set or cancel visit mark for events.

Simple Event Scheduler allows users to manage their events and time zone from WordPress dashboard. And super admin can manage all events and user time zones of the site.

Shortcode:
[se_scheduler_eventcalendar for_user="current|post_author|UID"]

for_user parameter:
- current – control is shown for the current signed-in user
- post_author – control is shown for the current post author inside the WordPress loop
- UID – non-negative integer, control is shown for WordPress user with this ID
- no for_user parameter or empty value – control shows events of all users

== Installation ==

To install the plugin, follow the steps below

1. Upload `simple-event-scheduler` to the `/wp-content/plugins/` directory OR install through admin Plugins page
2. Activate the plugin in 'Plugins' page in WordPress
3. Create pages or widgets with plugin shortcodes

== Frequently Asked Questions ==

= What PHP version should I have for using this plugin? =

You should have PHP Version 5.3 or higher.

== Screenshots ==

1. Event Calendar
2. Create Event Form
3. Super Admin Dashboard Page

== Changelog ==

= 1.0 =
* Plugin release.

== Upgrade Notice ==

= 1.0 =
First version of the plugin