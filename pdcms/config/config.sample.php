<?php
/*
 * config.sample.php
 * ------------------
 * The real configuration lives in config/config.php and reads values from the
 * environment (.env file or server environment variables). This sample only
 * documents which keys are required. DO NOT put real secrets in the repository.
 *
 * Required environment keys (set them in .env locally or in cPanel > Environment):
 *
 *   APP_NAME   = "Product Drive CMS"
 *   APP_ENV    = "production"        # local | production
 *   APP_DEBUG  = "false"            # true only for local development
 *   APP_BASE   = ""                # "/subfolder" if the app is not on the domain root
 *   APP_TZ     = "Asia/Jakarta"
 *
 *   DB_HOST    = "localhost"
 *   DB_PORT    = "3306"
 *   DB_NAME    = "yourcpaneluser_productdrive"
 *   DB_USER    = "yourcpaneluser_pdcms"
 *   DB_PASS    = "<set-on-server-only>"
 *
 * Copy .env.example to .env and fill in the values. Never commit .env.
 */
