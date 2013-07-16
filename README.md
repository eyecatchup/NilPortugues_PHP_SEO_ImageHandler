## NilPortugues\SEO\ImageHandler
A PHP class that extracts image tags from HTML code and allows keeping a record of the processed images in a data structure such as a database.

This class is meant to be used both in the back-end and front-end of a site.
 * In the backend, this class should handle processing large chuncks of HTML input.
    * Processing HTML means replacing valid HTML image tags to custom image tag placeholders. This process is reversed in the front-end.
    * Processing HTML means resizing images that have been enlarged or reduced using pixel measures.
    * Processing HTML means downloading external images and resizing as previously explained if necessary.
    * Processing HTML means making sure no two identical images will be sitting on the server's image directory or from different sources. All images are unique and duplicates are disregarded if these exist in the image database.
 * The images extracted should manipulable using the back-end, to edit alt, title or the image file name. This changes will be reflected elsewhere by replacing the placeholders with the real data.
 * In the front-end, this class should be only used to replace the image placeholders for their valid HTML image tags equivalents.

### Todo:
* Complete ImageHandlerTest.php
* Add proper documentation.


### Author
Nil Portugués Calderó
 - <contact@nilportugues.com>
 - http://nilportugues.com

### License

 This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.