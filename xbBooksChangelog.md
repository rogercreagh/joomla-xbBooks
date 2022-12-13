### xbBooks Changelog

**v1.0.0** = v0.12.0.2 12th Dec 2022

- removed ext link hint option (see [blog](https://blog.crosborne.uk/link-hinting-revisited))
- legacy files deleted on upgrade
- many minor bugs fixed

------

v0.11.0

- Missing Language strings added (still some hard-coded english in code)
- People & Chars configs moved to xbPeople

------

v0.10.0

- shared custom fields moved to xbPeople
- updated models and layouts with code improvements
- improved category selection by partial hierarchy
- improved tag selection, tag grouping
- bug with modal height interfering with other extensions fixed

------

**v0.9.9.9** : 9th November 2022

new features:

- Option to allow no-name for reviewer
- Site list views using summary/detail rather than mouseover popups
- Sample categories moved to subcategories of Imported category
- Default categories for Books and Reviews created on on install
- Book dates changed to be first/last read with unread books having both dates null and presented at end of list. 

Bug fixes and code tidys

- shared functions moved from xbBooks Helper to xbCulture Helper in xbPeople
- Import sample data was creating double categories
- first_read and last_read correctly updating with reviews
- tag and category path display improved
- error on sorting people by role fixed
- various format and layout improvements for consistency between views
- first/last read dates now date only rather than datetime (database change)
- fixed error in tag counts on admin tags view
- tag filter code improved

------

v0.9.9.5 : 30th July 2022

- person and character site views now use xbPeople, lists still available in xbFilms for film-only people and chars
- Many many bugfixes over the past year - too many to list here!

------

**v0.9.5** : 10th May 2021
new features:

  - site view category filters can now specify a subtree of categories to use (allows separate parents for films, revies and people) 
  - blog view now shows dates more prominently, includes rating-only entries, shows average rating if more than one rating for film.


 bugfixes: 
  - blog view author names incorrect
  - edit book date now required field rather than defaulting to today
  - edit book quick rating default category now as per option setting
  - review edit default titles and aliases tidied up and consistent
  - review edit rev_date date now required field rather than defaulting to today
  - site list views search filter row not staying open when filter selected
  - blog view clarified category filtering to include film and review categories

------

v0.9.4.1 : 29th April 2021 - minor bug fixes 
 - corrected missing language strings in menu xml's

------

**v0.9.4**   : 17th April 2021 - first JED release
 - major reworking of elements common to all xbCulture components
 - common stylesheet now installed as part of xbPeople component
 - common language strings file now installed as part of xbPeople component
 - xbBooks will no longer work without xbPeople being installed - use the package install file, or install xbPeople before installing xbFilms

