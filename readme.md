iPeer 3.0.6
---------------------------
This is a maintenance release. It fixes a few bugs related to the building block API and web interface.
* Fixed a bug in the api
* Modified groupMembers and enrolment in the api
* Updated guard plugin
* Fixed a bug when adding 0 member to course using API
* Fixed zero_mark bug for rubric view and rubric evaluation forms
* Fixed non-existing users added to group as id 0 by API
* Fixed #480, removed auth error on login page when redirect from app root
* Moved API log into logs/api.log
* Fixed a style issue
* Fixed failed to add user when user data has tab (\t) in API

iPeer 3.0.5
---------------------------
This is a maintenance release. It fixes a few bugs related to the building block API and web interface.
* Fixed events add/edit toggle
* Fixed #491 Adding class member failed when duplicate username in request
* Fixed #490 Denied access to admin/department page
* Fixed #489 Invalid signature error when installed on subdirectory

iPeer 3.0.4
---------------------------
This is a maintenance release. It is recommended to upgrade to this version as it fixes a one critical bug.
* Fix submissions for simple evaluation are not stored correctly

iPeer 3.0.3
----------------------------
This is a maintenance release. It is recommended to upgrade to this version as it fixes a one critical bug.
* Fix incorrect question number in mixeval
* Using student first submission timestamp as late penalty criteria
* Fix mixeval descriptor overflow issue

iPeer 3.0.2
----------------------------
This is a maintenance release. It is recommended to upgrade to this version as it fixes a few critical bugs.
* Disable submission confirmation email
* Fix some bugs & outdated instructions
* Fix change password for editProfile
* Fix some bugs for email templates
* Fix warnings caused by empty courseId in group controller
* Fix user can't login with session transfer if there is an old session
* Unify the views in v1 controller
* Fix preview button on add event page in IE and FF
* Allow fall back to mail in template email
* Fix incorrect logging when sending submission email failed.
* Fix missing names in the email after creating user
* Disable the type checking for upload csv file for now
* Fix #471, add extra file type to allow upload for user import
* Fix that email failed to send when stmp parameters are not set
* Fix #464. The student view of mixeval result was not showing correctly.
* Update guard to allow login in with PUID as username in cwl module

iPeer 3.0.1
-----------------------------
This is a maintenance release. It is recommended to upgrade to this version as it fixes a few critical bugs.
 * Fix old survey event can't be edited
 * Fix penalty display when only 1 penalty present.
 * Remove result release date from event form for surveys
 * Fix it couldn't add question in survey in IE and Firefox
 * Fix a teammaker xml issue when student survey response id is NULL
 * Fix Survey result link on instructor/admin home.
 * Fix old survey result do not show on viewEvaluationResult
 * Exclude dropped student from survey result summary
 * Fix survey save for "Choose any of" questions
 * Fix unable to add text answers to surveys
 * Fix master question losing responses & deletion
 * Fix previous submissions not loading
 * Fix Survey Question - Add/Remove Answer not working

iPeer 3.0.0
-----------------------------
More than 74 bug fixes or improvements from 3.0.0 Beta
 * Better CSV export. More efficient and better format for process
 * Upgrade scripts allowing to upgrade from 2.x
 * Better student home page
 * Confirmation email after evaluation submitted
 * New color theme
 * And more

iPeer 3.0.0 Beta
-----------------------------
New Features:
 * Improved Installation and Upgrade Wizard
 * The new Tutur Role and Faculty Admin Role
 * Evaluation Absence and Mark Deduction 
 * Fine-grainded Access Control 
 * New APIs
 * Group Export
 * Group Email
 * CWL/Shibboleth, LDAP Authentication
 * Basic LTI Support
 * Internationalization (i18N) support
 * Upgrade to CakePHP Framework 1.3
 * A lot bug fixes
 * And more

iPeer 2.2
-----------------------------
Changelog from 2.1
1) Students are now able to view released evaluation results (grades and comments)
     These results are anonymous, and randomly ordered.
     Fixed for Rubrics and Mixed Evaluations.
2) Import from CSV changes for Students/Groups:
     Email and password are optional. Password will be randomly generated if it is omitted from the import file.
     Column orders are changed. Please see the sample file on import page.
     Mac/Windows Excel CSV format is supported.
     Default course is set to blank when importing student.
3) Export Changes
     Corrected Export Evaluation result calculations.
     Evaluation result export with details.
4) Copy mixed evaluation function is now working.
5) Small GUI Changes:
     Changed phrasing and language to make things more clear.
     More clear notice if student did not finish all questions on student portal.
6) General bug fixes, including some code clean-up.
7) New Listing component
     Replaces most lists in iPeer with a unified component
     (with notable exception of Advanced Search).
     Easy to use for end-users, and to implement and augment by developers.


Changelog from 2.0

1) Over 180 bugs are fixed
2) Major browsers are supported
3) PHP 5.3 is supported
4) Improved security and permission checking
5) Improved installation script and new upgrade script
6) Now exports comments in the CSV file
7) Import Students/Groups from CSV file now functional
8) Instructors can only add students to the courses the instructor teaches
9) Instructors can no longer remove students from courses the instructor does not teach
10) Instructors can no longer remove students from the database
11) An instructor can no longer remove himself/herself from the course
12) Group email links are removed temporarily

Changelog from 2.0.8

1) Bug fix: #1725231 , #1725229 (see bugs list in SF for details)
2) Modify default login page to remove unnecessary JavaScript codes


upgrade from 1.6:

1. copy the ipeer_export folder OUTSIDE of your iPeer 2 folder
2. run it from your browser http://yourserverpath/ipeer_export/ipeer_export.php
3. type the user/pass and whatnot to get the xml file of your database
4. save it for now and continue on to installation of iPeer 2.

iPeer 2 install:

requirements:
mod_rewrite for apache
PHP 4.3.10+ with GD extension
MYSQL 4+
PEAR with XML_RPC module (optional, only needed when using CWL)

1.  run http://yourserverpath/youripeerpath/install   (trailing slash may be required)
1b. create a database in MySql
2.  follow the instructions for the install
2a. if you are upgrading, select UPGRADE and load the xml file there.
--     in install4, make sure you set the absolute path to your path!
3.  once the install is complete, delete controllers/install_controller.php and /ipeer_export directory.

please note:

1. The database config file /app/config/database.php must be writable during installation. 
2. After installation, please make database.php read only. It is VERY important.
3. To change file permission, you may either use a FTP client or do it directly through command line (if you have
shell access to your server. In unix, you may use chmod command to do it)

please report any bugs you find on sourceforge. we can't fix it if we don't know about it.

any suggestion or question? please let us know

troubleshooting:

-if you type http://yourserverpath/youripeerpath/ and you get http://yourserverpath/loginout/login, your mod_rewrite
is not set up properly. make sure the line in http.conf has 'AllowOverride All'.
