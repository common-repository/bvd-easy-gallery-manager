<div class="wrap">
    <h2>BVD Gallery Manager Plugin Details</h2>
    <div class="designed-by-wrapper-admin-page">
        <p>Plugin designed and developed by<br/><a href="https://www.balcom-vetillo.com/" target="_blank">Balcom-Vetillo Design</a>.</p>
        <a href="https://www.balcom-vetillo.com/" target="_blank"><img src="<?php echo plugins_url('assets/images/BVD-Logo-vert.png', __FILE__); ?>" /></a>
    </div>
    <div class="bvd-gallery-admin-page-wrapper">
        <?php
        if($this->free_trial_submit_success) {
            if($this->free_trial_error) {
                ?>
                <div id="gm-free-trial-message" class="error">
                    <p>An error occurred. Your free trail could not be started. <?php echo $this->free_trial_error; ?></p>
                </div>
            <?php
            } else {
            ?>
            <div id="gm-free-trial-message" class="updated">
                <p>Your free trial has been successfully started. Please check your email for further directions.</p>
            </div>
            <?php
            }
        } else {
            if($this->free_trial_error) {
                ?>
                <div id="gm-free-trial-message" class="error">
                    <p>An error occurred. Your free trail could not be started. <?php echo $this->free_trial_error; ?></p>
                </div>
            <?php
            }
        }
        ?>
        <div class="bvd-gallery-basic-usage-wrapper">
            <h3>Basic Usage</h3>
            <p>To create a new gallery, go to the <a href="/wp-admin/edit.php?post_type=bvd-gallery">plugin's admin page</a> and click on the Add New button. Give the gallery a title.</p>
            <p>To begin adding images to the gallery, click the Manage Gallery button found in the Gallery Thumbnails box. This will open the native WordPress media manager in gallery mode. To upload new images into the gallery, you can either drag and drop them anywhere inside the media manager or click the Select Files button to browse for the photos on your computer. To add photos that are already uploaded to WordPress, click the Add to Gallery link found in the left sidebar of the media manager. Select the images to add to the gallery by clicking on them and then clicking the Add to gallery button in the lower right of the media manager. You can edit the photo's caption if you are displaying captions. When you are finished, click the Update gallery button in the lower right of the media manager. <strong>Don't forget to always click the Update button under the Publish box on the right to save all of your changes.</strong></p>
            <p>The options that control how the gallery is displayed can be found in the Gallery Options box.</p>
        </div>

        <?php
        if (!$this->check_pro_key()) {
            $hidden_value = 'set-pro-key';
            $pro_key = '';
            $submit_value = 'Submit Key';
        } else {
            $hidden_value = 'deactivate-pro-key';
            $pro_key = $this->get_pro_key();
            $submit_value = 'Deactivate Key';
        }
        ?>
        <div class="pro-key-submit-form-wrapper">
            <h3>
                Pro Version
            </h3>
            <div class="pro-key-submit-form-info">
                <?php
                if (!$this->check_pro_key()) {
                    ?>
                    <p>The BVD Gallery Manager plugin has an optional pro version that can be unlocked by purchasing a Pro Key. The pro version will unlock additional plugin settings as well as the Front End Gallery Manager.</p>
                    <p>If you have a Pro Key, enter it in the form below to activate the pro version of this plugin.</p>
                    <?php
                } else {
                    ?>
                    <p>Your Pro Key has been activated!</p>
                    <p>You can deactivate your Pro key by submitting the form below.</p>
                    <?php
                }
                ?>
            </div>
            <form action="" method="post" class="pro-key-submit-form">
                <input type="hidden" name="bvd-gallery-action" value="<?php echo $hidden_value; ?>" />
                <div class="form-section">
                    <div class="form-section-left">
                        <label for="pro-key">Pro Key</label>
                    </div>
                    <div class="form-section-right">
                        <?php
                        if (!empty($pro_key)) {
                            ?>
                            <input type="text" name="pro-key" id="pro-key" placeholder="Pro Key" value="<?php echo $pro_key; ?>">
                            <?php
                        } else {
                            ?>
                            <input type="text" name="pro-key" id="pro-key" placeholder="Pro Key">
                            <?php
                        }
                        ?>
                    </div>
                    <div style="clear:left;"></div>
                </div>
                <div class="form-section">
                    <input type="submit" value="<?php echo $submit_value; ?>" />
                </div>
            </form>
        </div>

        <div class="bvd-gallery-frontend-edit-shortcode-wrapper">
            <h3>Front End Editing</h3>
            <?php
            if (!$this->check_pro_key()) {
                ?>
                <p>Front End Editing is included in the pro version of this plugin. You can try out a free 7 day trial before you buy a Pro Key.</p>
                <?php
            } else {
                ?>
                <p>This plugin includes an option to easily add photos to any gallery right in your site's theme. The gallery must first be created in the <a href="/wp-admin/edit.php?post_type=bvd-gallery">plugin's admin page</a>. After a gallery is created, you can create a new page on your site and enter the shortcode found below in the content editor on that page. Just go to that page, login with your WordPress credentials and select the gallery you want to manage. You can add photos, remove photos, add captions to photos, and upload new photos all from this page on your site. <strong>Make sure to hit the save button when you are finished to save all of your changes.</strong></p>
                <p><strong>Front End Editing Shortcode:</strong> [bvd-gallery-frontend-admin]</p>

                <div class="bvd-gallery-frontend-edit-option-wrapper">
                    <h4>Front End Editing Option</h4>
                    <form action="" method="post">
                        <input type="hidden" name="bvd-gallery-action" value="save-frontend-editing-option" />
                        <div class="form-section">
                            <?php
                            $editing_option = get_option("bvd-gallery-frontend-editing-type-option");
                            if($editing_option == 'show') {
                                $checkbox_checked = 'checked';
                            } else {
                                $checkbox_checked = '';
                            }
                            ?>
                            <label><input type="checkbox" name="bvd-frontend-editing-option" value="1" <?php echo $checkbox_checked; ?>> Show Advanced Front End Editing Button</label>
                            <div class="frontend-editing-option-hint">
                                Check this if you want to be able to add, remove, and upload images as well as change the order of images and add captions to images. If this is unchecked, only a basic drag and drop uploader will be used and all further editing will need to be done in the plugin admin.
                            </div>
                            <div style="clear:left;"></div>
                        </div>
                        
                        <div class="form-section">
                            <input type="submit" value="Submit" />
                        </div>
                    </form>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
        if($this->check_free_trial()) {
        ?>
        <div class="bvd-gallery-frontend-edit-trial-wrapper">
            <h3>Front End Editing 7 Day Free Trial</h3>
            <form method="post" action="" class="bvd-gallery-frontend-edit-trial-form">
                <input type="hidden" name="bvd-gallery-action" value="frontend-editing-trial" />
                <div class="form-section">
                    <div class="form-section-left">
                        <label for="frontend-edit-7-day-trail-email">Email (required)</label>
                    </div>
                    <div class="form-section-right">
                        <input type="email" name="frontend-edit-7-day-trial-email" id="frontend-edit-7-day-trial-email" />                   
                    </div>
                </div>
                <button type="submit">Start 7 Day Free Trial</button>
            </form>
        </div>
        <?php
        }
        ?>
    </div>
</div>