<?php
$atts = shortcode_atts(
        array(
    'id' => '',
    'count' => 'value_default'
        ), $atts, 'bvd-gallery');

if (!empty($atts['id'])) {
    
    $values = get_post_custom($atts['id']);
    if (isset($values['bvd_images_gallery'])) {
        // The json decode and base64 decode return an array of image ids
        $ids = json_decode(base64_decode($values['bvd_images_gallery'][0]));
    } else {
        $ids = array();
    }

    if (isset($values['bvd-gallery-gallery-type'])) {
        $gallery_type = json_decode(base64_decode($values['bvd-gallery-gallery-type'][0]));
    } else {
        $gallery_type = 'lightbox';
    }

    if ($gallery_type == 'slider') {
        if (isset($values['bvd-gallery-slides-visible'])) {
            $gallery_slides_visible = json_decode(base64_decode($values['bvd-gallery-slides-visible'][0]));
        } else {
            $gallery_slides_visible = 3;
        }
        
        if(empty($gallery_slides_visible)) {
            $gallery_slides_visible = 3;
        }

        if (isset($values['bvd-gallery-slides-slide'])) {
            $gallery_slides_slide = json_decode(base64_decode($values['bvd-gallery-slides-slide'][0]));
        } else {
            $gallery_slides_slide = 3;
        }
        
        if(empty($gallery_slides_slide)) {
            $gallery_slides_slide = 3;
        }
    } else {
        if (isset($values['bvd-gallery-thumbs-padding'])) {
            $gallery_thumbs_padding = json_decode(base64_decode($values['bvd-gallery-thumbs-padding'][0]));
        } else {
            $gallery_thumbs_padding = 5;
        }
        
        /*if(empty($gallery_thumbs_padding)) {
            $gallery_thumbs_padding = 5;
        }*/
    }

    if (isset($values['bvd-gallery-captions'])) {
        $gallery_captions = json_decode(base64_decode($values['bvd-gallery-captions'][0]));
    } else {
        $gallery_captions = false;
    }

    if (isset($values['bvd-gallery-effect'])) {
        $gallery_effect = json_decode(base64_decode($values['bvd-gallery-effect'][0]));
    } else {
        $gallery_effect = 'slide';
    }

    if (isset($values['bvd-gallery-infinite'])) {
        $gallery_infinite = json_decode(base64_decode($values['bvd-gallery-infinite'][0]));
    } else {
        $gallery_infinite = false;
    }

    if (isset($values['bvd-gallery-pagination'])) {
        $gallery_pagination = json_decode(base64_decode($values['bvd-gallery-pagination'][0]));
    } else {
        $gallery_pagination = false;
    }

    if ($gallery_pagination) {
        if (isset($values['bvd-gallery-pagination-type'])) {
            $gallery_pagination_type = json_decode(base64_decode($values['bvd-gallery-pagination-type'][0]));
        } else {
            $gallery_pagination_type = 'bullets';
        }
    }

    if (isset($values['bvd-gallery-image-size'])) {
        $gallery_image_size = json_decode(base64_decode($values['bvd-gallery-image-size'][0]));
    } else {
        $gallery_image_size = 'full';
    }

    if ($gallery_image_size == 'custom') {
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

        $gallery_image_size = array($gallery_image_size_width, $gallery_image_size_height);
    }
    
    if (isset($values['bvd-gallery-image-count'])) {
        $gallery_image_count = json_decode(base64_decode($values['bvd-gallery-image-count'][0]));
    } else {
        $gallery_image_count = -1;
    }
    
    if($atts['count'] !== 'value_default') {
        $gallery_image_count = $atts['count'];
    }
    
    ?>
    <div class="bvd-gallery-container">
        <?php
        if ($gallery_type == 'lightbox') {
            if ($gallery_image_count == -1) {
                foreach ($ids as $id) {
                    $full_image = wp_get_attachment_image_src($id, 'full');
                    $attrbts = $this->bvd_get_attachment_attributes($id);
                    
                    $attr = array(
                        'alt' => $attrbts['caption'],
                        'style' => 'padding:'.$gallery_thumbs_padding.'px'
                    );
                    ?>
                    <a href="<?php echo $full_image[0]; ?>" class="bvd-gallery-image-item" title="<?php echo $attrbts['caption']; ?>">
                        <?php echo wp_get_attachment_image($id, $gallery_image_size, false, $attr); ?>
                    </a>
                    <?php
                }
            } else {
                $i = 1;
                foreach ($ids as $id) {
                    if ($i <= $gallery_image_count) {
                        $full_image = wp_get_attachment_image_src($id, 'full');
                        $attrbts = $this->bvd_get_attachment_attributes($id);
                        
                        $attr = array(
                            'alt' => $attrbts['caption'],
                            'style' => 'padding:'.$gallery_thumbs_padding.'px'
                        );
                        ?>
                        <a href="<?php echo $full_image[0]; ?>" class="bvd-gallery-image-item" title="<?php echo $attrbts['caption']; ?>">
                            <?php echo wp_get_attachment_image($id, $gallery_image_size, false, $attr); ?>
                        </a>
                        <?php
                    }
                    $i++;
                }
            }
        } else {
            foreach ($ids as $id) {
                //$full_image = wp_get_attachment_image_src($id, 'full');
                $attrbts = $this->bvd_get_attachment_attributes($id);
                    
                $attr = array(
                    'title' => $attrbts['caption']
                );
                ?>
                <span class="bvd-gallery-image-item-slider">
                    <?php echo wp_get_attachment_image($id, $gallery_image_size, false, $attr); ?>
                </span>
                <?php
            }
        }
        ?>
    </div>
    <script>
        jQuery(function ($) {
            <?php
            if ($gallery_type == 'lightbox') {
                ?>
                $('.bvd-gallery-container a').tosrus({
                    caption: {
                        <?php
                        if($gallery_captions) {
                            ?>
                            add: true,
                            attributes: ["title"]
                            <?php
                        } else {
                            ?>
                            add: false
                            <?php
                        }
                        ?>
                    },
                    pagination: {
                        <?php
                        if($gallery_pagination) {
                            ?>
                            add: true,
                            type: '<?php echo $gallery_pagination_type; ?>'
                            <?php
                        } else {
                            ?>
                            add: false
                            <?php
                        }
                        ?>
                    },
                    <?php
                    if($gallery_infinite) {
                        ?>
                        infinite: true,
                        <?php
                    } else {
                        ?>
                        infinite: false,
                        <?php
                    }
                    ?>
                    effect: '<?php echo $gallery_effect; ?>'
                });   
                <?php
            } else {
                ?>
                $('.bvd-gallery-container').tosrus({
                    caption: {
                        add: false
                    },
                    pagination: {
                        <?php
                        if($gallery_pagination) {
                            ?>
                            add: true,
                            type: '<?php echo $gallery_pagination_type; ?>'
                            <?php
                        } else {
                            ?>
                            add: false
                            <?php
                        }
                        ?>
                    },
                    <?php
                    if($gallery_infinite) {
                        ?>
                        infinite: true,
                        <?php
                    } else {
                        ?>
                        infinite: false,
                        <?php
                    }
                    ?>
                    effect: '<?php echo $gallery_effect; ?>',
                    slides: {
                        visible: <?php echo $gallery_slides_visible; ?>,
                        slide: <?php echo $gallery_slides_slide; ?>
                    }
                });   
                <?php
            }
            ?>
        });
    </script>
    <?php
}