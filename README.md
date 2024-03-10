# tours-toolkit

This extension replaces PR #40645.
It will add missing packaging facilities to the guided tours.

Exporting tours is important for 
- creators of tours who would like to export their work and import it into client's websites,
- third party extension developers who would like to create tours and make them available to their users,
- keep a directory of tours, available, downloadable and easily installable.

The tour toolkit will 
- export tours in the json format,
- grab and store media,
- grab and create language files (when tours are multi-lingual),
- package all in an installable zip file. The package will contain an installer that will import tours, media and language files.

Once packaged, the idea is to have a specific JED directory where people will be able to find extended core tours, third-party tours and other miscellaneous tours.

Update 03/09/2024

The toolkit includes a plugin and a component.
- the plugin adds the action to export the tour as json file to each tour view
- the component contains an import feature so you can import tours.
- there are buttons to go back and forth the toolkit and the guided tours.

## The toolkit page
![guidedtourstoolkit](https://github.com/joomla-extensions/tours-toolkit/assets/5964177/ed78f41c-9f31-4247-b82b-c0b4378ec5b8)

## Export a tour
![guidedtourstoolkit_exporttour](https://github.com/joomla-extensions/tours-toolkit/assets/5964177/04af9b1c-ffe1-499d-9254-0fcb28b2dcda)
