# REST OAI

## Overview
This module, made for **Drupal 8.4.x**, exposes Dublin Core metadata (actually, the nodes' content of your Drupal site involved on Dublin Core elements) as XML or JSON, giving ***a way to supply your content according to the OAI Protocol for Metadata Harvesting***.

### OAI Verbs
| Verb                | Done? |
|---------------------|:-----:|
| Identify            |   ✔   |
| GetRecord           |   ✔   |
| ListRecords         |   ✔   |
| ListSets            |   ✔   |
| ListMetadataFormats |   ✔   |
| ListIdentifiers     |   ✖   |

## Dependencies (modules)
* REST
* REST UI

## Dependencies (content structure)
The field dependencies are specified on *OAI_GetResource.php* for now...

But here's the basic list:

| Element           |Is a D.C. element?|                    Field Name |
|-------------------|:----------------:|------------------------------:|
| Title             |         ✔        | field_item_title              |
| Creator Type      |         ✖        | field_item_creator            |
| Creator (Pers.)   |         ✔        | field_item_creator_personal   |
| Creator (Corp.)   |         ✔        | field_item_creator_corporate  |
| Creator (Conf.)   |         ✔        | field_item_creator_conference |
| Contributor       |         ✔        | field_item_contributor        |
| Subject           |         ✔        | field_item_subject            |
| Coverage          |         ✔        | field_item_coverage           |
| Description       |         ✔        | field_item_description        |
| Citation          |         ✔        | field_item_citation           |
| Type              |         ✔        | field_item_type               |
| Format            |         ✔        | field_item_format             |
| Language          |         ✔        | field_item_language           |
| Publisher's Place |         ✔        | field_item_publisher_place    |
| Publisher         |         ✔        | field_item_publisher          |
| Related Reference |         ✔        | field_item_relation_reference |
| Related Document  |         ✔        | field_item_relation_document  |
| Source            |         ✔        | field_item_source             |
| Rights            |         ✔        | field_item_rights             |
| Digital Document  |         ✖        | field_item_digital_doc        |
| Document (Upload) |         ✔        | field_item_digital_doc_upload |
| Document (URL)    |         ✔        | field_item_digital_doc_url    |
| Date of Issue     |         ✔        | field_item_date_issued        |
| Status            |         ✖        | field_item_status             |

## Why is it so raw and simple?
Well then, I'm still a **noob** at PHP and even Drupal. Can't do much magic at the time, so get grateful for what you have right now, ok...

## Important
You might interest in removing the *.git folder* of this module, after downloading it. I remember to have some issues with git, specially when you want to push your site in Github, so here's ma tip, bro.

## Status of the Module
**IN DEVELOPMENT** (gimme some days or a week, please...).
