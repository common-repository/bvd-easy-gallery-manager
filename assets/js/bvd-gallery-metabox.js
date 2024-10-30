jQuery(function($){
    function select(shortcode) {
        var shortcode = wp.shortcode.next('gallery', shortcode);
        var defaultPostId = wp.media.gallery.defaults.id;
        var attachments;
        var selection;

        // Bail if we didn't match the shortcode or all of the content.
        if (!shortcode) {
            return;
        }

        shortcode = shortcode.shortcode;

        if (typeof shortcode.get('id') != 'undefined' && typeof defaultPostId != 'undefined') {
            shortcode.set('id', defaultPostId);
        }

        attachments = wp.media.gallery.attachments(shortcode);
        selection = new wp.media.model.Selection(attachments.models, {
            props   : attachments.props.toJSON(),
            multiple: true
        });

        selection.gallery = attachments.gallery;

        /*
         * Fetch the query's attachments, and then break ties from the query to allow for sorting.
         */
        selection.more().done(function () {
            // Break ties with the query.
            selection.props.set({
                query: false
            });
            selection.unmirror();
            selection.props.unset('orderby');
        });
        return selection;
    }
    
    // The click event for the gallery manage button
    $('.bvd_gallery_manage_gallery').click(function() {
        // Create the shortcode from the current ids in the hidden field
        var originalGalleryImages = $('#bvd_gallery_ids').val();
        var gallerysc = '[gallery ids="' + originalGalleryImages + '"]';
        
        if(originalGalleryImages.length > 1) {
            var selection = select(gallerysc);
            console.log('has-images');
            var frame = wp.media({
                frame   : 'post',
                title   : wp.media.view.l10n.editGalleryTitle,
                multiple: true,
                state   : 'gallery-edit',
                editing : true,
                selection: selection
            });
        } else {
            console.log('no-images');
            var frame = wp.media({
                frame   : 'post',
                title   : wp.media.view.l10n.editGalleryTitle,
                multiple: true,
                state   : 'gallery-library',
                editing : true
            });
        }

        frame.on('update', function (g) {
            // We fill the array with all ids from the images in the gallery
            var id_array = [];
            $.each(g.models, function(id, img) { 
                if(originalGalleryImages.indexOf(img.id) === -1) {
                    id_array.unshift(img.id);
                } else {
                    id_array.push(img.id); 
                }
            });
            // Make comma separated list from array and set the hidden value
            $('#bvd_gallery_ids').val(id_array.join(","));
            // On the next post this field will be send to the save hook in WP
            
            //Ajax to update the Metabox
            $.ajax({
                url: bvdGalleryAjax.ajaxurl,
                type: "POST",
                dataType: "JSON",
                data: {
                    action: "adminUpdateGallery",
                    galleryIDs : id_array.join(",")
                }
            }).success(function (response) {
                $('#bvd-gallery-manager-gallery-admin-container').html(response);
            });
        });

        frame.open();
        
        
       /* // Create the shortcode from the current ids in the hidden field
        var originalGalleryImages = $('#bvd_gallery_ids').val();
        if(originalGalleryImages.length > 1) {
            var gallerysc = '[gallery ids="' + originalGalleryImages + '"]';
            console.log('has ids');
        } else {
            var gallerysc = '[gallery ids="null"]';
            console.log('no ids');
        }
        // Open the gallery with the shortcode and bind to the update event
        wp.media.gallery.edit(gallerysc).on('update', function(g) {
            // We fill the array with all ids from the images in the gallery
            var id_array = [];
            $.each(g.models, function(id, img) { 
                if(originalGalleryImages.indexOf(img.id) === -1) {
                    id_array.unshift(img.id);
                } else {
                    id_array.push(img.id); 
                }
            });
            // Make comma separated list from array and set the hidden value
            $('#bvd_gallery_ids').val(id_array.join(","));
            // On the next post this field will be send to the save hook in WP
            
            //Ajax to update the Metabox
            $.ajax({
                url: bvdGalleryAjax.ajaxurl,
                type: "POST",
                dataType: "JSON",
                data: {
                    action: "adminUpdateGallery",
                    galleryIDs : id_array.join(",")
                }
            }).success(function (response) {
                $('#bvd-gallery-manager-gallery-admin-container').html(response);
            });
        });*/
    });
    
    $('#bvd-gallery-option-pagination').on('change', function(){
        if($(this).val() == 1) {
            $('#pagination-hidden-wrapper').slideDown();
        } else {
            $('#pagination-hidden-wrapper').slideUp();
        }
    });
    
    $('#bvd-gallery-option-gallery-type').on('change', function(){
        if($(this).val() == 'slider') {
            $('#slides-hidden-wrapper').slideDown();
            $('#lightbox-padding-hidden-wrapper').slideUp();
        } else {
            $('#slides-hidden-wrapper').slideUp();
            $('#lightbox-padding-hidden-wrapper').slideDown();
        }
    });
    
    $('#bvd-gallery-option-image-size').on('change', function(){
        if($(this).val() == 'custom') {
            $('#image-size-hidden-wrapper').slideDown();
        } else {
            $('#image-size-hidden-wrapper').slideUp();
        }
    });
});