# tours-toolkit

This extension replaces PR #40645.
It adds missing packaging facilities to the guided tours.

Exporting tours is important for 
- creators of tours who would like to export their work and import it into client's websites,
- third party extension developers who would like to create tours and make them available to their users,
- keep a directory of tours, available, downloadable and easily installable.

The tour packager will 
- export tours in the json format,
- grab and store media,
- grab and create language files (when tours are multi-lingual),
- package all in an installable zip file. The package will contain an installer that will import tours, media and language files.

Once packaged, the idea is to have a specific JED directory where people will be able to find extended core tours, third-party tours and other miscellaneous tours.

Stay tuned!
