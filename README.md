## Survey Log Identifier
### A REDCap External Module
---
### Description
The Survey Log Identifier module adds an identifier into REDCap logs for selected surveys. This is a requirement to comply with FDA's 21 CFR Part 11 regulations. The user can select which survey(s) to add identifiers to and can choose which REDCap field holds that identifier.

### Usage
After downloading and enabling this module on your REDCap instance, enable it in a project and set the configuration options. This module only works on the project level.

1. The **id field** is where this module will look to find the identifier. 
*Take care not to include true PHI as your identifier unless you know what you're doing.*
* Leaving the **id field** setting blank will cause the module to use the project's record ID field as the identifier.
* Note that the **id field** cannot exist on a repeatable form or repeatable instrument.
2. The **instrument** setting chooses which survey(s) to apply this module to.
* Leaving the **instrument** setting blank will apply this module to all surveys in the project.
3. The **prefix** setting adds optional text to prepend to the identifier in the logs.

**Note to administrators:** Do not enable this on all projects by default. Although the default settings will not truly allow identification of the survey respondent, it is still not advisable unless required.

### Help
For assistance with this module, contact <ins>Andrew Poppe</ins> either by:
* using email link in external module description in REDCap: ![Image of REDCap Module Description](https://i.imgur.com/foCFAgY.png)
* opening an issue on github 


