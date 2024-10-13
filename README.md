# tours-toolkit

This extension replaces PR #40645.
It will add missing packaging facilities to the guided tours.

Exporting tours is important for
- creators of tours who would like to export their work and import it into client's websites,
- third party extension developers who would like to create tours and make them available to their users,
- keep a directory of tours, available, downloadable and easily installable.

As of now, the toolkit includes a plugin and a component that allow to:
- export a tour as json, SQL or SQL with language keys files to each tour view,
- import tours as json,
- import tour steps from a CSV file,
- look for targets with the selector tool,
- go back and forth between the toolkit and the guided tours.

Check Releases for further information.
pkg_guidedtourstoolkit.zip will install both extensions and enable the plugin.

## Export a tour to json, SQL, SQL with language keys
![image](https://github.com/user-attachments/assets/25632adc-1138-44ac-9c5b-b8f6603ca3f3)

## Import tours as json
![guidedtourstoolkit](https://github.com/joomla-extensions/tours-toolkit/assets/5964177/ed78f41c-9f31-4247-b82b-c0b4378ec5b8)

## Import steps as CSV
![image](https://github.com/user-attachments/assets/a8f7774f-3ef0-4ed9-99ca-4422a35a99e4)

## Select elements on the page with the selector tool

Enable the selector tool.
![image](https://github.com/user-attachments/assets/22314f2a-caa5-49d9-b2e1-35fb26a3bb14)

The selector tool needs to be turned on to start the selection. Turn off to navigate to other pages.
![image](https://github.com/user-attachments/assets/7738acca-64ab-42d4-9d24-5ee2af2414c2)

Select an element on the page and copy the result.
![image](https://github.com/user-attachments/assets/72611625-d0db-4394-ae8c-1dd8ad30bef1)

## The packaging tool

This is a work in progress.

The tours toolkit packaging tool will:
- export tours in both SQL formats supported by Joomla,
- grab and store media,
- create language files (when tours are multi-lingual),
- package all in an installable zip file.

Once packaged, the idea is to have a specific JED directory where people will be able to find extended core tours, third-party tours and other miscellaneous tours.
