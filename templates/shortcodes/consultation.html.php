<div class="applynow-con consultation-form">

    <div class="row row-main">

        <!-- Left Section: Apply Now Form -->

        <div class="col-md-12 text-center">

            <h2 class="apply-title"><?php esc_html_e('Consultation', 'sit-connect'); ?></h2>

        </div>

        <div class="col-md-12">

            <form enctype="multipart/form-data" action="?" method="post">

                <input type="hidden" name="uni_id" value="<?= $uni_details['uni_id'] ?>">

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label"><?php esc_html_e('First Name', 'sit-connect'); ?> <span class="text-danger">*</span></label>

                        <input type="text" name="first_name" class="form-control custom-input" required>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label"><?php esc_html_e('Last Name', 'sit-connect'); ?> <span class="text-danger">*</span></label>

                        <input type="text" name="last_name" class="form-control custom-input" required>

                    </div>

                    <div class="col-md-12">

                        <label class="form-label"><?php esc_html_e('Email Address', 'sit-connect'); ?> <span class="text-danger">*</span></label>

                        <input type="email" name="email" class="form-control custom-input" required>

                    </div>

                    <div class="col-md-12">

                        <label class="form-label"><?php esc_html_e('Phone Number', 'sit-connect'); ?> <span class="text-danger">*</span></label>

                        <input type="tel" name="phone" class="form-control custom-input" required>

                    </div>

                    <div class="col-md-12">

                        <label class="form-label"><?php esc_html_e('Preferred study Level', 'sit-connect'); ?></label>

                        <select class="form-select custom-input" name="study_level">
                            <option>Please select</option>
                            <option value="Associate's">Associate's</option>
                            <option value="Bachelor's">Bachelor's</option>
                            <option value="Master's">Master's</option>
                            <option value="Phd">Phd</option>

                        </select>

                    </div>
                    <div class="col-md-12">

                        <label class="form-label"><?php esc_html_e('Preferred study Year', 'sit-connect'); ?></label>

                        <select class="form-select custom-input" name="study_year">

                            <option>Please select</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>

                        </select>

                    </div>

                </div>



                <button type="submit" class="btn btn-danger apply-btn"><?php esc_html_e('Apply Now', 'sit-connect'); ?></button>

            </form>

        </div>



    </div>

</div>