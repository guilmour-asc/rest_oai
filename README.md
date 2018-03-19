# REST OAI

## Overview
This module, made for **Drupal 8.4.x**, exposes Dublin Core metadata (actually, the nodes' content of your Drupal site involved on Dublin Core elements) as XML or JSON, giving ***a way to supply your content according to the OAI Protocol for Metadata Harvesting***.

## Dependencies (modules)
* REST
* REST UI

## Dependencies (content structure)
The field dependencies are specified on *OAI_GetResource.php* for now...
But here's the basic list:
| Element |Is a D.C. element?| Field Name |
|---------|:------------:|-----------:|
| Title   | yes | field_item_title |
| Creator Type | no | field_item_creator |
| Creator (Pers.) | yes | field_item_creator_personal |
| Creator (Corp.) | yes | field_item_creator_corporate |
| Creator (Conf.) | yes | field_item_creator_conference |
| Contributor | yes | field_item_contributor |
| Subject | yes | field_item_subject |
| Coverage | yes | field_item_coverage |
| Description | yes | field_item_description |
| Citation | yes | field_item_citation |
| Type | yes | field_item_type |
| Format | yes | field_item_format |
| Language | yes | field_item_language |
| Publisher's Place | yes | field_item_publisher_place |
| Publisher | yes | field_item_publisher |
| Related Reference | yes | field_item_relation_reference |
| Related Document | yes | field_item_relation_document |
| Source | yes | field_item_source |
| Rights | yes | field_item_rights |
| Digital Document | no | field_item_digital_doc |
| Document (Upload) | yes | field_item_digital_doc_upload |
| Document (URL) | yes | field_item_digital_doc_url |
| Date of Issue | yes | field_item_date_issued |
| Status | no | field_item_status |

## Why is it so raw and simple?
Well then, I'm still a **noob** at PHP and even Drupal. Can't do much magic at the time, so get grateful for what you have right now, ok...

## Important
You might interest in removing the *.git folder* of this module, after downloading it. I remember to have some issues with git, specially when you want to push your site in Github, so here's ma tip, bro.

## Status of the Module
**IN DEVELOPMENT** (gimme some days or a week, please...).