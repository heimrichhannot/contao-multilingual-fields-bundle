# Changelog

All notable changes to this project will be documented in this file.

## [0.5.0] - 2021-07-14

- removed `PaletteManipulator` reflection and solved palette handling with existing methods (thanks to @aschempp)

## [0.4.2] - 2021-07-06

- fixed `onload` callback for `ModulePassword`

## [0.4.1] - 2021-06-28

- fixed sql issues

## [0.4.0] - 2021-06-24

- added support for multilingual alias fields (see README.md for details)
- added new insert tags `mf_event_url`, `mf_news_url` and `mf_faq_url` taking into account the translated alias field

## [0.3.6] - 2021-06-24

- added auto translation in `isVisibleElement` hook for `tl_content`

## [0.3.5] - 2021-06-21

- added `sql_condition` and `sql_condition_values` config parameters

## [0.3.4] - 2021-06-18

- fixed submitOnChange handling

## [0.3.3] - 2021-06-18

- fixed palette handling

## [0.3.2] - 2021-06-18

- fixed README.md

## [0.3.1] - 2021-06-18

- fixed README.md

## [0.3.0] - 2021-06-18

- fixed palette handling -> now only fields in the current palette are displayed in language edit mode; palettes don't
  need to be set manually but are retrieved automatically
- added optional language field for `tl_content` in order to hide certain content elements according to the language
  (needed for rsce) -> usage is discouraged because of unnecessary duplicate data
- added a new feature for hiding content elements based on a new field called `mf_language`

## [0.2.5] - 2021-06-11

- fixed palette handling and visualization

## [0.2.4] - 2021-06-11

- fixed README.md

## [0.2.3] - 2021-04-19

- enhanced README.md

## [0.2.2] - 2021-04-19

- added meta field links to the fields' dca
- enhanced README.md

## [0.2.1] - 2021-04-16

- fixed assets path and restricted to backend
- fixed multiple language issue
- code optimization

## [0.2.0] - 2021-04-16

- added English translation
- added missing readonly styles for various fields
- added strings eval for connection reasons
- enhanced README

## [0.1.1] - 2021-04-16

- removed fallback language as unnecessary ;-)

## [0.1.0] - 2021-04-16

- initial release
