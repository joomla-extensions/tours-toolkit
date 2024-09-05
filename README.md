# tours-toolkit

This extension replaces PR #40645.
It will add missing packaging facilities to the guided tours.

Exporting tours is important for 
- creators of tours who would like to export their work and import it into client's websites,
- third party extension developers who would like to create tours and make them available to their users,
- keep a directory of tours, available, downloadable and easily installable.

The tours toolkit will 
- export tours in the json format,
- grab and store media,
- grab and create language files (when tours are multi-lingual),
- package all in an installable zip file. The package will contain an installer that will import tours, media and language files.

Once packaged, the idea is to have a specific JED directory where people will be able to find extended core tours, third-party tours and other miscellaneous tours.

As of now, the toolkit includes a plugin and a component that allow to:
- export a tour as json, SQL or SQL with language keys files to each tour view,
- import tours as json,
- import tour steps from a CSV file,
- go back and forth between the toolkit and the guided tours.

Check Releases for further information.
pkg_guidedtourstoolkit.zip will install both extensions and enable the plugin.

## Import tours as json
![guidedtourstoolkit](https://github.com/joomla-extensions/tours-toolkit/assets/5964177/ed78f41c-9f31-4247-b82b-c0b4378ec5b8)

## Export a tour to json, SQL, SQL with language keys
![guidedtourstoolkit_exporttour](https://github.com/joomla-extensions/tours-toolkit/assets/5964177/04af9b1c-ffe1-499d-9254-0fcb28b2dcda)

## Import steps as CSV
![import_as_csv](https://github.com/joomla-extensions/tours-toolkit/assets/5964177/b85324e8-1fb2-42b1-a38e-b0b706bfbdc7)
