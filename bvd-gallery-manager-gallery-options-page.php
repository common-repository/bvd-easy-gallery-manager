<?php
// Here we get the current option values of the gallery
$values = get_post_custom($post->ID);
if (isset($values['bvd-gallery-gallery-type'])) {
    $gallery_type = json_decode(base64_decode($values['bvd-gallery-gallery-type'][0]));
    switch ($gallery_type) {
        case 'lightbox' :
            $gallery_type_lightbox = 'selected';
            $gallery_type_slider = '';
            break;
        case 'slider' :
            $gallery_type_lightbox = '';
            $gallery_type_slider = 'selected';
            break;
    }
} else {
    $gallery_type_lightbox = 'selected';
    $gallery_type_slider = '';
}

if (isset($values['bvd-gallery-slides-visible'])) {
    $gallery_slides_visible = json_decode(base64_decode($values['bvd-gallery-slides-visible'][0]));
} else {
    $gallery_slides_visible = '3';
}

if (isset($values['bvd-gallery-slides-slide'])) {
    $gallery_slides_slide = json_decode(base64_decode($values['bvd-gallery-slides-slide'][0]));
} else {
    $gallery_slides_slide = '3';
}

if (isset($values['bvd-gallery-thumbs-padding'])) {
    $gallery_thumbs_padding = json_decode(base64_decode($values['bvd-gallery-thumbs-padding'][0]));
} else {
    $gallery_thumbs_padding = '5';
}

if (isset($values['bvd-gallery-captions'])) {
    $gallery_captions = json_decode(base64_decode($values['bvd-gallery-captions'][0]));
    if ($gallery_captions) {
        $gallery_captions_true = 'selected';
        $gallery_captions_false = '';
    } else {
        $gallery_captions_true = '';
        $gallery_captions_false = 'selected';
    }
} else {
    $gallery_captions_true = '';
    $gallery_captions_false = 'selected';
}

if (isset($values['bvd-gallery-effect'])) {
    $gallery_effect = json_decode(base64_decode($values['bvd-gallery-effect'][0]));
    switch ($gallery_effect) {
        case 'slide' :
            $gallery_effect_slide = 'selected';
            $gallery_effect_fade = '';
            break;
        case 'fade' :
            $gallery_effect_slide = '';
            $gallery_effect_fade = 'selected';
            break;
    }
} else {
    $gallery_effect_slide = 'selected';
    $gallery_effect_fade = '';
}

if (isset($values['bvd-gallery-infinite'])) {
    $gallery_infinite = json_decode(base64_decode($values['bvd-gallery-infinite'][0]));
    if ($gallery_infinite) {
        $gallery_infinite_true = 'selected';
        $gallery_infinite_false = '';
    } else {
        $gallery_infinite_true = '';
        $gallery_infinite_false = 'selected';
    }
} else {
    $gallery_infinite_true = '';
    $gallery_infinite_false = 'selected';
}

if (isset($values['bvd-gallery-pagination'])) {
    $gallery_pagination = json_decode(base64_decode($values['bvd-gallery-pagination'][0]));
    if ($gallery_pagination) {
        $gallery_pagination_true = 'selected';
        $gallery_pagination_false = '';
    } else {
        $gallery_pagination_true = '';
        $gallery_pagination_false = 'selected';
    }
} else {
    $gallery_pagination_true = '';
    $gallery_pagination_false = 'selected';
}

if (isset($values['bvd-gallery-pagination-type'])) {
    $gallery_pagination_type = json_decode(base64_decode($values['bvd-gallery-pagination-type'][0]));
    switch ($gallery_pagination_type) {
        case 'bullets' :
            $gallery_pagination_type_bullets = 'selected';
            $gallery_pagination_type_thumbnails = '';
            break;
        case 'thumbnails' :
            $gallery_pagination_type_bullets = '';
            $gallery_pagination_type_thumbnails = 'selected';
            break;
    }
} else {
    $gallery_pagination_type_bullets = 'selected';
    $gallery_pagination_type_thumbnails = '';
}

$sizes = $this->get_image_sizes();

if (isset($values['bvd-gallery-image-size'])) {
    $gallery_image_size = json_decode(base64_decode($values['bvd-gallery-image-size'][0]));
} else {
    $gallery_image_size = 'medium';
}

if (isset($values['bvd-gallery-image-size-width'])) {
    $gallery_image_size_width = json_decode(base64_decode($values['bvd-gallery-image-size-width'][0]));
} else {
    $gallery_image_size_width = '';
}

if (isset($values['bvd-gallery-image-size-height'])) {
    $gallery_image_size_height = json_decode(base64_decode($values['bvd-gallery-image-size-height'][0]));
} else {
    $gallery_image_size_height = '';
}

if (isset($values['bvd-gallery-image-count'])) {
    $gallery_image_count = json_decode(base64_decode($values['bvd-gallery-image-count'][0]));
} else {
    $gallery_image_count = -1;
}

if (isset($values['bvd-gallery-page'])) {
    $gallery_page = json_decode(base64_decode($values['bvd-gallery-page'][0]));
} else {
    $gallery_page = '';
}

// We display the gallery options
?>
<div class="bvd-gallery-metabox-form-wrapper">
    <label for="bvd-gallery-option-gallery-type">Type of Gallery</label>
    <select name="bvd-gallery-option-gallery-type" id="bvd-gallery-option-gallery-type">
        <option value="0">Gallery Type</option>
        <option value="lightbox" <?php echo $gallery_type_lightbox; ?>>Lightbox</option>
    </select>
    <div class="bvd-gallery-option-hint">
        Lightbox will display a grid of images at the size selected below and the full size image on click.<br/>
     
            A Pro Key will unlock a second gallery type: Carousel - Horizontal Slider. This type will display the images on a single line at the size selected and slide them horizontally.

    </div>
</div>

<?php
if ($gallery_type_lightbox == 'selected') {
    $style = 'style="display:block;"';
} else {
    $style = '';
}
?>
<div id="lightbox-padding-hidden-wrapper" <?php echo $style; ?>>
    <div class="bvd-gallery-metabox-form-wrapper">
        <label for="bvd-gallery-option-thumbs-padding">Padding Around Images in Grid</label>
        <input type="text" name="bvd-gallery-option-thumbs-padding" id="bvd-gallery-option-thumbs-padding" value="<?php echo $gallery_thumbs_padding; ?>" /><span class="bvd-gallery-input-post-addon">px</span>
        <div class="bvd-gallery-option-hint">
            Choose how much padding to put around each image in the grid.
        </div>
    </div>
</div>

<?php
if ($gallery_type_slider == 'selected') {
    $style = 'style="display:block;"';
} else {
    $style = '';
}
?>
<div id="slides-hidden-wrapper" <?php echo $style; ?>>
    <div class="bvd-gallery-metabox-form-wrapper">
        <label for="bvd-gallery-option-slides-visible">Number of Visible Slides</label>
        <input type="text" name="bvd-gallery-option-slides-visible" id="bvd-gallery-option-slides-visible" value="<?php echo $gallery_slides_visible; ?>" />
        <div class="bvd-gallery-option-hint">
            Choose how many images are visible at once in the horizontal slider.
        </div>
    </div>

    <div class="bvd-gallery-metabox-form-wrapper">
        <label for="bvd-gallery-option-slides-slide">Number of Slides to Slide</label>
        <input type="text" name="bvd-gallery-option-slides-slide" id="bvd-gallery-option-slides-slide" value="<?php echo $gallery_slides_slide; ?>" />
        <div class="bvd-gallery-option-hint">
            Choose the number of slides to slide at once in the horizontal slider.
        </div>
    </div>
</div>

<div class="bvd-gallery-metabox-form-wrapper">
    <label for="bvd-gallery-option-captions">Display Captions</label>
    <select name="bvd-gallery-option-captions" id="bvd-gallery-option-captions">
        <option value="0">Display Captions Option</option>
        <option value="1" <?php echo $gallery_captions_true; ?>>Yes</option>
        <option value="0" <?php echo $gallery_captions_false; ?>>No</option>
    </select>
    <div class="bvd-gallery-option-hint">
        Choose whether or not to show image captions. You can set captions on images in the gallery editor.
    </div>
</div>

<div class="bvd-gallery-metabox-form-wrapper">
    <label for="bvd-gallery-option-effect">Transition Effect</label>
    <select name="bvd-gallery-option-effect" id="bvd-gallery-option-effect">
        <option value="0">Transition Effect</option>
        <option value="slide" <?php echo $gallery_effect_slide; ?>>Slide</option>
    </select>
    <div class="bvd-gallery-option-hint">
       
            A second transition effect is unlocked with a Pro Key: Fade.
           
    </div>
</div>

<div class="bvd-gallery-metabox-form-wrapper">
    <label for="bvd-gallery-option-infinite">Infinite Rotation</label>
    <select name="bvd-gallery-option-infinite" id="bvd-gallery-option-infinite">
        <option value="0">Infinite Rotation</option>
     
        <option value="0" <?php echo $gallery_infinite_false; ?>>Off</option>
    </select>
    <div class="bvd-gallery-option-hint">
     
            The option to create an infinite sliding gallery is unlocked with a Pro Key.
   
    </div>
</div>

<div class="bvd-gallery-metabox-form-wrapper">        
    <label for="bvd-gallery-option-pagination">Show Pagination</label>
    <select name="bvd-gallery-option-pagination" id="bvd-gallery-option-pagination">
        <option value="0">Pagination Option</option>
        <option value="1" <?php echo $gallery_pagination_true; ?>>Yes</option>
        <option value="0" <?php echo $gallery_pagination_false; ?>>No</option>
    </select>
    <div class="bvd-gallery-option-hint">
        Choose if you want to display a type of pagination on the slider.
    </div>
</div>
<?php
if ($gallery_pagination_true == 'selected') {
    $style = 'style="display:block;"';
} else {
    $style = '';
}
?>
<div id="pagination-hidden-wrapper" <?php echo $style; ?>>
    <div class="bvd-gallery-metabox-form-wrapper">
        <label for="bvd-gallery-option-pagination-type">Choose Pagination Type</label>
        <select name="bvd-gallery-option-pagination-type" id="bvd-gallery-option-pagination-type">
            <option value="0">Pagination Type</option>
            <option value="bullets" <?php echo $gallery_pagination_type_bullets; ?>>Bullets</option>
        
        </select>
        <div class="bvd-gallery-option-hint">
            Bullets pagination type will show a dot (bullet point) for each image in the slider.<br/>
     
                The Thumbnails pagination type will unlock with a Pro Key.
      
        </div>
    </div>
</div>

<div class="bvd-gallery-metabox-form-wrapper">
    <label for="bvd-gallery-option-image-size">Choose Image Size</label>
    <select name="bvd-gallery-option-image-size" id="bvd-gallery-option-image-size">
        <option value="0">Image Size</option>
        <?php
        foreach ($sizes as $key => $value) {
            if ($gallery_image_size == $key) {
                $gallery_image_size_select = 'selected';
            } else {
                $gallery_image_size_select = '';
            }

            if ($value['crop'] == 1) {
                $image_crop = ' - cropped';
            } else {
                $image_crop = '';
            }
            ?>
            <option value="<?php echo $key; ?>" <?php echo $gallery_image_size_select; ?>><?php echo $key . ' - ' . $value['width'] . 'x' . $value['height'] . $image_crop; ?></option>
            <?php
        }

        if ($gallery_image_size == 'custom') {
            ?>
            <option value="custom" selected>Custom Size</option>
            <?php
        } else {
            ?>
            <option value="custom">Custom Size</option>
            <?php
        }
        ?>
    </select>
    <div class="bvd-gallery-option-hint">
        These are all the defined sizes in WordPress. You can also choose to set your own size.
    </div>
</div>
<?php
if ($gallery_image_size == 'custom') {
    $style = 'style="display:block;"';
} else {
    $style = '';
}
?>
<div id="image-size-hidden-wrapper" <?php echo $style; ?>>
    <div class="bvd-gallery-metabox-form-wrapper">
        <label for="bvd-gallery-option-image-size-width">Image Width</label>
        <input type="text" name="bvd-gallery-option-image-size-width" id="bvd-gallery-option-image-size-width" value="<?php echo $gallery_image_size_width; ?>" /><span class="bvd-gallery-input-post-addon">px</span>
        <div class="bvd-gallery-option-hint">
            Set the largest allowed image width in pixels.
        </div>
    </div>

    <div class="bvd-gallery-metabox-form-wrapper">
        <label for="bvd-gallery-option-image-size-height">Image Height</label>
        <input type="text" name="bvd-gallery-option-image-size-height" id="bvd-gallery-option-image-size-height" value="<?php echo $gallery_image_size_height; ?>" /><span class="bvd-gallery-input-post-addon">px</span>
        <div class="bvd-gallery-option-hint">
            Set the largest allowed image height in pixels.<br/><br/>Setting a width and height will not necessarily make the images that exact size. The image will be scaled proportionally. To make an image be cropped to exact dimensions, you need to define a custom image size and rebuild all the thumbnails.
        </div>
    </div>
</div>

<div class="bvd-gallery-metabox-form-wrapper">
    <label for="bvd-gallery-option-image-count">Image Count</label>
    <input type="text" name="bvd-gallery-option-image-count" id="bvd-gallery-option-image-count" value="<?php echo $gallery_image_count; ?>" />
    <div class="bvd-gallery-option-hint">
        Choose how many images you want to display from this gallery. Enter -1 to show all of the images.
        
    </div>
</div>

<div class="bvd-gallery-metabox-form-wrapper">
    <label for="bvd-gallery-option-gallery-page">Gallery Page</label>
    <select name="bvd-gallery-option-gallery-page" id="bvd-gallery-option-gallery-page"> 
        <option value="0">Select a Page</option> 
        <?php 
        $pages = get_pages(); 
        foreach ( $pages as $page ) {
            if($gallery_page == $page->ID) {
                $option = '<option value="' . $page->ID . '" selected>';
            } else {
                $option = '<option value="' . $page->ID . '">';
            }
            $option .= $page->post_title;
            $option .= '</option>';
            echo $option;
        }
        ?>
    </select>
    <div class="bvd-gallery-option-hint">
        The page to display the gallery on. This will automatically add the shortcode to the selected page.
    </div>
</div>