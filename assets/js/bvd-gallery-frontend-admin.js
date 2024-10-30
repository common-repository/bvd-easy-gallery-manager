Dropzone.autoDiscover = false;
jQuery(function($){
    var bvdFrontendGalleryBasicUploadForm;
    var bvdFrontendGalleryBasicUploadFormPhoneCamera;
    
    if($('#bvd-gallery-select-gallery').length > 0) {
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
        
        $('#bvd-gallery-select-gallery').on('change', function(){
            if($(this).val() != 0) {
                $('#bvd-gallery-frontend-admin-form-append-container').html('<i class="fa fa-spinner fa-spin"></i>');
                
                var galleryID = $(this).val();
                
                //Ajax to fetch the basic gallery upload
                $.ajax({
                    url: bvdGalleryFrontendAjax.ajaxurl,
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        action: "frontendAdminGetBasicUpload",
                        galleryID : galleryID
                    }
                }).success(function (response) {
                    $('#bvd-gallery-frontend-admin-basic-upload-container').html(response);
                    
                    bvdFrontendGalleryBasicUploadForm = new Dropzone("form#bvd-frontend-gallery-basic-upload-form", {
                        acceptedFiles: 'image/*',
                        maxFiles: 10
                    });
                    
                    bvdFrontendGalleryBasicUploadFormPhoneCamera = new Dropzone("form#bvd-frontend-gallery-basic-upload-form-phone-camera", {
                        acceptedFiles: 'image/*',
                        maxFiles: 1
                    });
                });
                
                //Ajax to fetch the advanced gallery editing
                $.ajax({
                    url: bvdGalleryFrontendAjax.ajaxurl,
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        action: "frontendAdminGetAdvancedUpload",
                        galleryID : galleryID
                    }
                }).success(function (response) {
                    $('#bvd-gallery-frontend-admin-form-append-container').html(response);
                });
            }
        });
        
        $('#bvd-gallery-frontend-admin-form').on('click', '#bvd-gallery-frontend-admin-btn', function() {
            //console.log('click');
            // Create the shortcode from the current ids in the hidden field
            var originalGalleryImages = $('#bvd-gallery-frontend-admin-images').val();
            var gallerysc = '[gallery ids="' + originalGalleryImages + '"]';
            var selection = select(gallerysc);
            // Open the gallery with the shortcode and bind to the update event
            //wp.media.controller.Library.prototype.defaults.contentUserSetting = false;
            /*wp.media.gallery.edit(gallerysc).on('update', function(g) {
                // We fill the array with all ids from the images in the gallery
                var id_array = [];
                //console.log(g.models);
                $.each(g.models, function(id, img) {
                    if(originalGalleryImages.indexOf(img.id) === -1) {
                        id_array.unshift(img.id);
                    } else {
                        id_array.push(img.id); 
                    }
                });
                // Make comma separated list from array and set the hidden value
                $('#bvd-gallery-frontend-admin-images').val(id_array.join(","));
                // On the next post this field will be send to the save hook in WP

                //Ajax to update the Metabox
                $.ajax({
                    url: bvdGalleryFrontendAjax.ajaxurl,
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        action: "frontendAdminGetGallery",
                        galleryIDs : id_array
                    }
                }).success(function (response) {
                    $('#bvd-gallery-frontend-admin-form-append-container').html(response);
                });
            });*/
            
            var frame = wp.media({
                frame   : 'post',
                title   : wp.media.view.l10n.editGalleryTitle,
                multiple: true,
                state   : 'gallery-library',
                editing : true,
                selection: selection
            });

            frame.on('update', function (g) {
                // We fill the array with all ids from the images in the gallery
                var id_array = [];
                //console.log(g.models);
                $.each(g.models, function(id, img) {
                    if(originalGalleryImages.indexOf(img.id) === -1) {
                        id_array.unshift(img.id);
                    } else {
                        id_array.push(img.id); 
                    }
                });
                // Make comma separated list from array and set the hidden value
                $('#bvd-gallery-frontend-admin-images').val(id_array.join(","));
                // On the next post this field will be send to the save hook in WP

                //Ajax to update the Metabox
                $.ajax({
                    url: bvdGalleryFrontendAjax.ajaxurl,
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        action: "frontendAdminGetAdvancedUpload",
                        galleryIDs : id_array
                    }
                }).success(function (response) {
                    $('#bvd-gallery-frontend-admin-form-append-container').html(response);
                    $('#bvd-gallery-frontend-admin-advanced-hidden').show();
                    $('#bvd-gallery-frontend-admin-basic-upload-container').hide();
                    $('#bvd-frontend-gallery-advanced-btn').hide();
                });
            });

            frame.open();
        });
        
        $('#bvd-gallery-frontend-admin-form').on('click', '#bvd-frontend-gallery-advanced-btn', function() {
            $('#bvd-gallery-frontend-admin-advanced-hidden').slideToggle();
            $('#bvd-gallery-frontend-admin-basic-upload-container').slideToggle();
            
            $(this).toggleClass('advanced-open');
            
            if($(this).hasClass('advanced-open')){
                $(this).text('basic');
            } else {
                $(this).text('advanced');
            }
        });
        
        $('.bvd-gallery-frontend-admin-container').on('click', '.dz-message.dz-default.dz-phone-camera-button-message', function() {
            bvdFrontendGalleryBasicUploadFormPhoneCamera.removeAllFiles();
        });
    }
});