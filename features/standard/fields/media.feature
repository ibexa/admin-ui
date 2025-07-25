@IbexaOSS @IbexaHeadless @IbexaExperience @IbexaCommerce @contentTypeFields
Feature: Content fields setting and editing
  As an administrator
  In order to manage content on my site
  I want to set, edit, copy and move content items.

  @javascript @APIUser:admin
  Scenario: Create an ImageAsset Content item and edit specified field
    Given I create "image" Content items in "/Media/Images/" in "eng-GB"
      | name             | image                                                        |
      | ImageAssetImage  | vendor/ibexa/behat/src/lib/Data/Images/small2.jpg  |
      And I create a 'Image Asset CT2' content type in "Content" with 'ImageAssetCT2' identifier
      | Field Type  | Name         | Identifier        | Required | Searchable | Translatable | Settings        |
      | Image Asset | ImageAField  | imageafield       | yes      | no	       | yes          |                 |
      And a "folder" Content item named "MediaFieldsContainer" exists in root
      | name                 | short_name           |
      | MediaFieldsContainer | MediaFieldsContainer |
      And I am logged as admin
      And I'm on Content view Page for MediaFieldsContainer
    When I start creating a new content 'Image Asset CT2'
      And I select "Media/Images/ImageAssetImage" from Image Asset Repository for "ImageAField" field
      And I perform the "Publish" action
    Then success notification that "Content published." appears
      And I should be on Content view Page for "MediaFieldsContainer/ImageAssetImage"
      And content attributes equal
      | label          | value      |
      | ImageAField    | small2.jpg |

  @javascript @APIUser:admin
  Scenario Outline: Create content item with given field
    Given I create a "<fieldName> CT" content type in "Content" with "<fieldInternalName>" identifier
      | Field Type  | Name        | Identifier          | Required | Searchable | Translatable | Settings       |
      | <fieldName> | Field       | <fieldInternalName> | no      | no	      | yes          | <fieldSettings>  |
      | Text line   | Name        | name	            | no      | yes	      | yes          |                  |
      And I am logged as admin
      And I'm on Content view Page for MediaFieldsContainer
    When I start creating a new content "<fieldName> CT"
      And I set content fields
        | label    | <label1>    | <label2> | <label3> |
        | Field    | <value1>    | <value2> | <value3> |
        | Name     | <fieldName> |          |          |
      And I perform the "Publish" action
    Then success notification that "Content published." appears
      And I should be on Content view Page for "MediaFieldsContainer/<contentItemName>"
      And content attributes equal
          | label    | <label1> | <label2> | <label3> |
          | Field    | <value1> | <value2> | <value3> |

    Examples:
      | fieldInternalName    | fieldName                    | fieldSettings                                                         |  label1   | value1           | label2     | value2                | label3  | value3      | contentItemName           |
      | ibexa_media              | Media                        |                                                                       | value     | video1.mp4       |            |                       |         |             | video1.mp4                |
      | ibexa_image              | Image                        |                                                                       | value     | image1.png       |            |                       |         |             | image1.png                |
      | ibexa_binaryfile         | File                         |                                                                       | value     | binary1.txt      |            |                       |         |             | binary1.txt               |
      | ibexa_image_asset         | Image Asset                  |                                                                       | value     | imageasset1.png  |            |                       |         |             | imageasset1.png           |

  @javascript @APIUser:admin
  Scenario Outline: Edit content item with given field
    Given I am logged as admin
      And I'm on Content view Page for "MediaFieldsContainer/<oldContentItemName>"
    When I perform the "Edit" action
      And I set content fields
        | label    | <label1> | <label2> | <label3> |
        | Field    | <value1> | <value2> | <value3> |
      And I perform the "Publish" action
    Then success notification that "Content published." appears
      And I should be on Content view Page for "MediaFieldsContainer/<newContentItemName>"
      And content attributes equal
        | label    | <label1> | <label2> | <label3> |
        | Field    | <value1> | <value2> | <value3> |

    Examples:
      | label1    | value1                   | label2     | value2                   | label3  | value3    | oldContentItemName        | newContentItemName           |
      | value     | video2.mp4               |            |                          |         |           | video1.mp4                | video2.mp4                   |
      | value     | image2.png               |            |                          |         |           | image1.png                | image2.png                   |
      | value     | binary2.txt              |            |                          |         |           | binary1.txt               | binary2.txt                  |
      | value     | imageasset2.png          |            |                          |         |           | imageasset1.png           | imageasset2.png              |
