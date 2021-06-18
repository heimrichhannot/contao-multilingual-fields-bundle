# Contao Multilingual Fields Bundle

This utility bundle offers functionality for backend editor's to translate arbitrary fields of any table-driven data
container in the Contao CMS.

## Features

- define which fields of which data containers should be translatable
- adds a button to the backend palette for switching into translation mode
- content elements: optionally add a select field to the backend palette in order to display the corresponding content
  element only for the selected language

## Impressions

![Edit languages button](docs/img/edit-languages-button.png)
The button for editing the languages (right) and the button for showing certain content elements only for the selected language (left, for `tl_content` only)

![Edit languages](docs/img/edit-languages.png)
Editors can specify which of the fields should be translated for which languages

## Use case

The use case for this bundle is to have an automatic and structured process for adding translatable fields to a data
container. Also, you get a nice and editor-friendly way to edit translations in the backend ;-)

## What doesn't the bundle do?

This bundle is a utility bundle for just bringing a nice and editor-friendly way to edit translations in the backend.
It's up to you how to use the fields.

**There's no out-of-the-box support for contao's frontend modules!**

But you can of course use the created fields in your templates (if the modules passes the data there).

## How does it work?

The bundle works quite simply: it looks into your project's `<project_dir>/config/config.yml` and based on that adds the
fields defined here as translatable fields.

Example: Imagine the following `config.yml` (position is the field representing the job of the member in the company):

```yaml
huh_multilingual_fields:
  fallback_language: de
  languages:
    - en
  data_containers:
    tl_member:
      fields:
        - { name: position }
```

This would end up in your DCA being automatically modified the following way:

```php
// ...
[
    // the original position field is nearly unchanged -> only some meta field links are set in eval
    'position' => [
        'exclude'   => true,
        'search'    => true,
        'inputType' => 'text',
        'eval'      => [
            'maxlength' => 128,
            'tl_class' => 'w50',
            'mandatory' => true,
            'isTranslatedField' => true,
            'translationConfig' => [
                'en' => [
                    'field' => 'en_position',
                    'selector' => 'en_translate_position'
]               ]
            ]
         ],
        'sql'       => "varchar(128) NOT NULL default ''"
    ],
    // automatically created: the selector field and the translation field
    // the selector field (subpalette and selector is also set)
    'en_translate_position' => [
        'label'     => ['Translate (English)', 'Click this option in order to translate the field for the given language.'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => [
            'tl_class' => 'w50',
            'submitOnChange' => true,
            'translationField' => 'en_position',
            'translatedField' => 'position'
        ],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    // the translation field
    'en_position' => [
        'label'     => ['Position (English)', '<the description as given>'], // generated automatically out of the label of the "position" field
        'exclude'   => true,
        'search'    => true,
        'inputType' => 'text',
        'eval'      => [
            'maxlength' => 128,
            'tl_class' => 'w50',
            'mandatory' => true,
            'translatedField' => 'position',
            'translationSelectorField' => 'en_translate_position'
         ],
        'sql'       => "varchar(128) NOT NULL default ''"
    ]
]
```

## Installation & configuration

1. Run `composer require heimrichhannot/contao-multilingual-fields-bundle`.
1. Create the file `<project_dir>/config/config.yml` if not already existing and specify your translatable fields:
   ```yaml
   huh_multilingual_fields:
     fallback_language: de
     content_language_select: # add a select field to content element palettes for displaying elements only for the specified language
       enabled: true # default: false
       types: # if not set, the select field is added to all tl_content types
         - text
     languages:
       - en
     data_containers:
       tl_member:
         fields:
           - { name: position }
   ```
1. Clear the project's cache (`<project_dir>/var/cache`).
1. Update the database. The new fields should be created now.

## Todo

1. translatable aliases
