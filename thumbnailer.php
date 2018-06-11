<?php
namespace Images;

// constants for cropImage and cropImageByAspectRatio functions
define("ALIGN_TOP_LEFT", -1);
define("ALIGN_CENTER", 0);
define("ALIGN_BOTTOM_RIGHT", 1);

class Thumbnailer
{
	private $orig_img = null; // stores the image resource file
	private $filetype = null; // stored the filetype of the image that was loaded

	// list of filetypes and the text to use for outputting errors, the function to load the image and the function to save the image for the filetypes
	private $filetypes = [
		IMG_BMP  => ['name' => 'BMP',  'loadFunction' => 'ImageCreateFromBmp',  'saveFunction' => 'ImageBmp'],
		IMG_GIF  => ['name' => 'GIF',  'loadFunction' => 'ImageCreateFromGif',  'saveFunction' => 'ImageGif'],
		IMG_JPG  => ['name' => 'JPEG', 'loadFunction' => 'ImageCreateFromJpeg', 'saveFunction' => 'ImageJpeg'],
		IMG_PNG  => ['name' => 'PNG',  'loadFunction' => 'ImageCreateFromPng',  'saveFunction' => 'ImagePng'],
		IMG_WBMP => ['name' => 'WBMP', 'loadFunction' => 'ImageCreateFromWbmp', 'saveFunction' => 'ImageWbmp'],
		IMG_WEBP => ['name' => 'WEBP', 'loadFunction' => 'ImageCreateFromWebp', 'saveFunction' => 'ImageWebp']
	];

	/*
	void __construct(string $img)

	Purpose:    create the Thumbnailer object and optionally load the source image

	Parameters: string $img - optional, filename of image thumbnail will be made from

	Returns:    if $img is specified loadImage is called using it
	*/
	public function __construct(string $img = null)
	{
		if ($img !== null) {
			$this->loadImage($img);
		}
	}

	/*
	void __destruct()

	Purpose:    cleans up when the object is destroyed

	Parameters: none

	Returns:    nothing
	*/
	public function __destruct()
	{
		if ($this->imageLoaded()) {
			$this->unloadImage();
		}
	}

	/*
	void loadImage(string $img)

	Purpose:    load the image that the thumbnail will be made from

	Parameters: string $img - filename of image thumbnail will be made from

	Returns:    nothing
	*/
	public function loadImage(string $img)
	{
		if (!file_exists($img)) {
			throw new Exception("file '$img' doesn't exist");
			return;
		}

		if (!$filetype = exif_imagetype($img)) {
			throw new Exception('unknown file type');
			return;
		}

		$exif_filetypes = [
			IMAGETYPE_BMP  => IMG_BMP,
			IMAGETYPE_GIF  => IMG_GIF,
			IMAGETYPE_JPEG => IMG_JPG,
			IMAGETYPE_PNG  => IMG_PNG,
			IMAGETYPE_WBMP => IMG_WBMP,
			IMAGETYPE_WEBP => IMG_WEBP
		];


		if (!array_key_exists($filetype, $exif_filetypes)) {
			throw new Exception('unsupported filetype');
			return;
		}

		$filetype = $exif_filetypes[$filetype];

		$filetype_info = $this->filetypes[$filetype];

		if (!(imagetypes()&$filetype)) {
			throw new Exception($filetype_info['name'] . " support is not compiled for PHP");
			return;
		}

		$orig_img = $filetype_info['loadFunction']($img);

		if (!$orig_img) {
			throw new Exception('Image not created successfully');
			return;
		}


		if ($this->imageLoaded())
			$this->unloadImage();

		$this->orig_img = $orig_img;
		$this->filetype = $filetype;

		if ($filetype==IMG_JPG) {
			// rotate and flip image according to EXIF data
			$exif = @exif_read_data($img);

			if ($exif && isset($exif['Orientation'])) {
				$ort = $exif['Orientation'];

				if ($ort == 5 || $ort == 6)
					$orig_img = $this->rotateImage(270);
				if ($ort == 3 || $ort == 4)
					$orig_img = $this->rotateImage(180);
				if ($ort == 7 || $ort == 8)
					$orig_img = $this->rotateImage(90);

				if ($ort == 4 || $ort == 5 || $ort == 7)
					$this->flipImage(IMG_FLIP_HORIZONTAL);
			}
		}
	}

	/*
	void unloadImage()

	Purpose:    unloads the current image

	Parameters: none

	Returns:    nothing
	*/
	public function unloadImage()
	{
		if (!$this->imageLoaded()) {
			throw new Exception('Unable to unload image.  No image was loaded.');
			return;
		}

		imagedestroy($this->orig_img);
		$this->orig_img = null;
		$this->filetype = null;
	}

	/*
	bool imageLoaded()

	Purpose:    checks if an image is loaded

	Parameters: none

	Returns:    true if an image is loaded, false otherwise
	*/
	public function imageLoaded(): bool
	{
		return ($this->orig_img !== null) ? true : false;
	}

	/*
	array getImageSize()

	Purpose:    returns the size of the image

	Parameters: none

	Returns:    an array with key "x" for the width and "y" for the height
	*/
	public function getImageSize(): array
	{
		return [
			"x" => ImageSX($this->orig_img),
			"y" => ImageSY($this->orig_img)
		];
	}

	/*
	float getImageAspectRatio()

	Purpose:    returns the aspect ratio for the image

	Parameters: none

	Returns:    a float representing the aspect ratio of the image
	*/
	public function getImageAspectRatio(): float
	{
		$size = $this->getImageSize();
		return $size["x"]/$size["y"];
	}

	/*
	void makeThumbnail()

	Purpose:    shrinks the current image to the defined size

	Parameters: $max_x        - optional, Maximum width of thumbnail, ignored if -1
				$max_y        - optional, Maximum height of thumbnail, ignored if -1
				$force_resize - optional, force resizing the image if the thumbnail will be bigger that the original

	Returns:    nothing

	Notes:      if $max_x and $max_y are both -1, default values of 100 will be used
	*/
	public function makeThumbnail(int $max_x = -1, int $max_y = -1, bool $force_resize = false)
	{
		$orig_size = $this->getImageSize();
		
		#detemine how much to scale the thumbnail down by
		if ($max_x<=0 && $max_y<=0) {
			#no size constraints specified, use default values
			$max_x = 100;
			$max_y = 100;
		}

		#if only max height was specified
		if ($max_x<=0) {
			$scale = $orig_size['y']/$max_y;
		}
		#if only max width was specified
		elseif ($max_y<=0) {
			$scale = $orig_size['x']/$max_x;
		}
		#if both max height and max width were specified
		else {
			$scale = max($orig_size['x']/$max_x, $orig_size['y']/$max_y);
		}

		#if thumbnail will be bigger that the original and we want to prevent that
		if ($scale<1 && !$force_resize) {
			#keep thumbnail same size as original
			$new_size = [
				'x' => $orig_size['x'],
				'y' => $orig_size['y']
			];
		}
		else {
			#find dimensions of thumbnail
			$new_size = [
				'x' => floor($orig_size['x']/$scale),
				'y' => floor($orig_size['y']/$scale)
			];
		}

		$new_img = ImageCreateTrueColor($new_size['x'], $new_size['y']);
		
		if (!$new_img) {
			throw new Exception('image creation for thumbnail failed');
			return;
		}

		if (!ImageCopyResampled($new_img, $this->orig_img, 0, 0, 0, 0, $new_size['x'], $new_size['y'], $orig_size['x'], $orig_size['y'])) {
			throw new Exception('copying info for thumbnail failed');
			return;
		}

		imagedestroy($this->orig_img);
		$this->orig_img = $new_img;
	}

	/*
	void cropImage(int $width, int $height, int $alignment)

	Purpose:    crops the current image

	Parameters: $width     - new width for the image
				$height    - new height for the image
				$alignment - optional, alignment for cropping
							 options are ALIGN_TOP_LEFT, ALIGN_CENTER or ALIGN_BOTTOM_RIGHT

	Returns:    nothing
	*/
	public function cropImage(int $width, int $height, int $alignment = ALIGN_CENTER)
	{
		$currentSize = $this->getImageSize();
		$newSize = [
			'width' => $width,
			'height' => $height,
			'x' => 0,
			'y' => 0
		];

		switch ($alignment)
		{
			case ALIGN_TOP_LEFT:
				#no action required
				break;
			case ALIGN_CENTER:
				$newSize['x'] = floor(($currentSize['x'] - $newSize['width']) / 2);
				$newSize['y'] = floor(($currentSize['y'] - $newSize['height']) / 2);
				
				break;
			case ALIGN_BOTTOM_RIGHT:
				$newSize['x'] = $currentSize['x'] - $newSize['width'];
				$newSize['y'] = $currentSize['y'] - $newSize['height'];

				break;
			default:
				throw new Exception('invalid alignment specified');
				return;
		}

		$img = imagecreatetruecolor($newSize['width'], $newSize['height']);
		imagecopy($img, $this->orig_img, 0, 0, $newSize['x'], $newSize['y'], $newSize['width'], $newSize['height']);

		if (!$img) {
			throw new Exception('error cropping image');
			return;
		}

		$this->orig_img = $img;
	}

	/*
	void cropImageByAspectRatio(float $aspectRatio, int $alignment)

	Purpose:    crops the image to be a specific aspect ration

	Parameters: $aspectRatio - aspect ratio, calculated as width divided by height
				$alignment - optional, alignment for cropping, options are ALIGN_TOP_LEFT, ALIGN_CENTER or ALIGN_BOTTOM_RIGHT

	Returns:    nothing
	*/
	public function cropImageByAspectRatio(float $aspectRatio, int $alignment = ALIGN_CENTER)
	{
		$currentAspectRatio = $this->getImageAspectRatio();

		if ($aspectRatio == $currentAspectRatio)
			return;

		$currentSize = $this->getImageSize();
		$newSize = [
			'width' => $currentSize['x'],
			'height' => $currentSize['y'],
			'x' => 0,
			'y' => 0
		];

		if ($aspectRatio > $currentAspectRatio) { # too tall
			$newSize['height'] = $currentSize["x"]/$aspectRatio;
		}
		else { # too wide
			$newSize['width'] = $currentSize["y"]*$aspectRatio;
		}

		$this->cropImage($newSize['width'], $newSize['height'], $alignment);
	}

	/*
	void rotateImage(float $angle)

	Purpose:    rotates the current image

	Parameters: $angle - angle in degrees to rotate the image

	Returns:    nothing
	*/
	function rotateImage(float $angle)
	{
		$this->orig_img = imagerotate($this->orig_img, $angle, 0);
	}

	/*
	void flipImage(int $mode)

	Purpose:    unloads the current image

	Parameters: $mode - which way to flip the image, options are IMG_FLIP_HORIZONTAL, IMG_FLIP_VERTICAL or IMG_FLIP_BOTH

	Returns:    nothing
	*/
	function flipImage(int $mode)
	{
		if ($mode != IMG_FLIP_HORIZONTAL && $mode != IMG_FLIP_VERTICAL && $mode != IMG_FLIP_BOTH) {
			throw new Exception('Invalid mode given for flipImage');
			return;
		}

		if (!imageflip($this->orig_img, $mode)) {
			throw new Exception('Unable to flip image');
		}
	}

	/*
	void saveImage(string $thumbnail_filename, int $filetype, int $quality)

	Purpose:    saves the current image

	Parameters: $thumbnail_filename - optional, Filename thumbnail will saved to
									  if blank, the raw image stream will be output directly
				$filetype           - optional, file format to save the image as
									  options are IMG_BMP, IMG_GIF, IMG_JPG, IMG_PNG, IMG_WBMP, IMG_WEBP
									  if omitted or null, the same type as the original image will be used
				$quality            - optional, JPEG quality, valid values are 0(worst) - 100(best)
									  used for JPEGs only, 

	Returns:    nothing
	*/
	public function saveImage(string $thumbnail_filename = "", int $filetype = null, int $quality = 75)
	{
		if ($filetype===null)
			$filetype = $this->filetype;

		if (!array_key_exists($filetype, $this->filetypes)) {
			throw new Exception('unsupported filetype');
			return;
		}

		$filetype_info = $this->filetypes[$filetype];

		if (!(imagetypes()&$filetype)) {
			throw new Exception($filetype_info['name'] . " support is not compiled for PHP");
			return;
		}

		if ($filetype == IMG_JPG) {
			#force a valid value for quality
			$quality = self::clamp($quality, 0, 100);

			$filetype_info['saveFunction']($this->orig_img, $thumbnail_filename, $quality);
		}
		else {

			$filetype_info['saveFunction']($this->orig_img, $thumbnail_filename);
		}
	}

	/*
	int getFiletypes()

	Purpose:    returns the supported filetypes

	Parameters: none

	Returns:    an integer containing the supported filetypes or'ed together
	*/
	function getFiletypes(): int {
		$filetypes = 0;

		foreach ($this->filetypes as $filetype => $value)
			$filetypes |= $filetype;

		return $filetypes;
	}

	/*
	static Thumbnailer factory(string $img)

	Purpose:    create a new Thumbnailer object

	Parameters: string $img - optional, filename of image thumbnail will be made from

	Returns:    a new Thumbnailer object
	*/
	public static function factory(string $img = null): Thumbnailer
	{
		return new Thumbnailer($img);
	}

	/*
	static mixed clamp()

	Purpose:    restrict $value to range between $min and $max

	Parameters: $value - the value to retrict between $min and $max
				$min   - minimum value for $value
				$max   - maximum value for $value

	Returns:    $value if it's between $min and $max
				$min if $value is lower than $min
				$max if $value is higher than $max

	Notes:      function will swap the values for $min and $max if $min is more then $max
	*/
	private static function clamp($value, $min, $max)
	{
		return max(min($min, $max), min(max($min, $max), $value));
	}
}
