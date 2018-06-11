# thumbnailer
A PHP class used to create thumbnail images, includes functions for scaling, cropping, rotating and flipping images

## code sample

```php
require 'thumbnailer.php';

use Images\Thumbnailer;

$input_img = 'orig_img.png';
$output_img = 'thumb_img.png';
$aspect_ratio = 16/9;

$tn = new Images\Thumbnailer($input_img);
$tn->cropImageByAspectRatio($aspect_ratio);
$tn->makeThumbnail(200, 200);
$tn->saveImage($output_img);
```

## Constant List
used by cropImage and cropImageByAspectRatio functions  
ALIGN_TOP_LEFT  
ALIGN_CENTER  
ALIGN_BOTTOM_RIGHT


## Function List

**__construct(string $img = null)**

*Purpose:*  
create the Thumbnailer object and optionally load the source image

*Parameters:*  
string $img - optional, filename of image thumbnail will be made from, if $img is specified loadImage is called using it

Returns:*  
nothing

---

**__destruct()**

*Purpose:*  
cleans up when the object is destroyed

*Parameters:*  
none

*Returns:*  
nothing

---

**void loadImage(string $img)**

*Purpose:*  
load the image that the thumbnail will be made from

*Parameters:*  
string $img - filename of image thumbnail will be made from

*Returns:*  
nothing


---

**void unloadImage()**

*Purpose:*  
unloads the current image

*Parameters:*  
none

*Returns:*  
nothing



---

**bool imageLoaded()**

*Purpose:*  
checks if an image is loaded

*Parameters:*  
none

*Returns:*  
true if an image is loaded, false otherwise



---

**array getImageSize()**

*Purpose:*  
returns the size of the image

*Parameters:*  
none

*Returns:*  
an array with key "x" for the width and "y" for the height



---

**float getImageAspectRatio()**

*Purpose:*  
returns the aspect ratio for the image

*Parameters:*  
none

*Returns:*  
a float representing the aspect ratio of the image



---

**void makeThumbnail()**

*Purpose:*  
shrinks the current image to the defined size

*Parameters:*  
$max_x - optional, Maximum width of thumbnail, ignored if -1  
$max_y - optional, Maximum height of thumbnail, ignored if -1  
$force_resize - optional, force resizing the image if the thumbnail will be bigger that the original

*Returns:*  
nothing

*Notes:*
if $max_x and $max_y are both -1, default values of 100 will be used



---

**void cropImage(int $width, int $height, int $alignment)**

*Purpose:*  
crops the current image

*Parameters:*  
$width - new width for the image  
$height - new height for the image  
$alignment - optional, alignment for cropping, options are ALIGN_TOP_LEFT, ALIGN_CENTER or ALIGN_BOTTOM_RIGHT

*Returns:*  
nothing



---

**void cropImageByAspectRatio(float $aspectRatio, int $alignment)**

*Purpose:*  
crops the image to be a specific aspect ration

*Parameters:*  
$aspectRatio - aspect ratio, calculated as width divided by height  
$alignment - optional, alignment for cropping, options are ALIGN_TOP_LEFT, ALIGN_CENTER or ALIGN_BOTTOM_RIGHT

*Returns:*  
nothing



---

**void rotateImage(float $angle)**

*Purpose:*  
rotates the current image

*Parameters:*  
$angle - angle in degrees to rotate the image

*Returns:*  
nothing



---

**void flipImage(int $mode)**

*Purpose:*  
unloads the current image

*Parameters:*  
$mode - which way to flip the image, options are IMG_FLIP_HORIZONTAL, IMG_FLIP_VERTICAL or IMG_FLIP_BOTH

*Returns:*  
nothing



---

**void saveImage(string $thumbnail_filename, int $filetype, int $quality)**

*Purpose:*  
saves the current image

*Parameters:*  
$thumbnail_filename - optional, Filename thumbnail will saved to, if blank, the raw image stream will be output directly  
$filetype - optional, file format to save the image as, options are IMG_BMP, IMG_GIF, IMG_JPG, IMG_PNG, IMG_WBMP, IMG_WEBP, if omitted or null, the same type as the original image will be used  
$quality - optional, JPEG quality, valid values are 0(worst) - 100(best), used for JPEGs only, 

*Returns:*  
nothing



---

**int getFiletypes()**

*Purpose:*  
returns the supported filetypes

*Parameters:*  
none

*Returns:*  
an integer containing the supported filetypes or'ed together



---

**static Thumbnailer factory(string $img)**

*Purpose:*  
create a new Thumbnailer object

*Parameters:*  
string $img - optional, filename of image thumbnail will be made from

*Returns:*  
a new Thumbnailer object



