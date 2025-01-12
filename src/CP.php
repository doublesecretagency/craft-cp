<?php
/**
 * CP Helper for Double Secret Agency
 *
 * Internal tools for the Craft CMS control panel.
 *
 * @author    Double Secret Agency
 * @link      https://www.doublesecretagency.com/
 * @copyright Copyright (c) 2023 Double Secret Agency
 */

namespace doublesecretagency\cp;

use Craft;
use craft\base\Plugin;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\User;
use craft\events\RegisterElementSourcesEvent;
use craft\web\View;
use doublesecretagency\cp\web\twig\Extension;
use Throwable;
use yii\base\Event;

/**
 * CP plugin
 * @since 1.0.0
 *
 * @method static CP getInstance()
 */
class CP extends Plugin
{

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        // Run parent init
        parent::init();

        // Automatically log in if conditions are met
        $this->_autoLogin();

        // If this is a CP request
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            // Defer most setup tasks until Craft is fully initialized
            Craft::$app->onInit(function () {
                $this->_hideDashboard();
                $this->_hideUtilitiesBadge();
                $this->_hideAllEntries();
                $this->_showElementTotals();
            });
            // Register the Twig Extension
            Craft::$app->view->registerTwigExtension(new Extension());
        }
    }

    // ================================================================================ //

    /**
     * Automatically log in if:
     *  - Not in a `dev` environment
     *  - Running with DDEV
     *  - User is not already logged in
     *  - AUTOLOGIN environment variable is set
     *  - User specified by AUTOLOGIN exists
     */
    private function _autoLogin(): void
    {
        // If not a `dev` environment, bail
        if ('dev' !== Craft::$app->getConfig()->env) {
            return;
        }

        // If not running with DDEV, bail
        if (!getenv('DDEV_PROJECT')) {
            return;
        }

        try {
            // If user is already logged in, bail
            if (Craft::$app->getUser()->getIdentity()) {
                return;
            }
        } catch (Throwable $e) {
            // Bail if an error occurs
            return;
        }

        // Get the specified AUTOLOGIN account
        $autologin = getenv('AUTOLOGIN');

        // If no AUTOLOGIN value, bail
        if (!$autologin) {
            return;
        }

        // Get the user specified by AUTOLOGIN
        $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($autologin);

        // If user doesn't exist, bail
        if (!$user) {
            return;
        }

        // Log in as the specified user
        Craft::$app->getUser()->loginByUserId($user->id);
    }

    // ================================================================================ //

    /**
     * Hide the "Dashboard" link.
     */
    private function _hideDashboard(): void
    {
        // Get first segment of current URL
        $page = Craft::$app->getRequest()->getSegment(1);

        // If on the Dashboard page
        if ('dashboard' === $page) {
            // Redirect to the Entries section
            Craft::$app->getResponse()->redirect('entries');
        }

        // Hide the "Dashboard" link in the primary nav
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_TEMPLATE,
            static function () {
                // Hide the "Dashboard" link via CSS
                $css = '#nav-dashboard {display:none !important}';
                Craft::$app->getView()->registerCss($css);
            }
        );
    }

    /**
     * Hide the "Utilities" badge.
     */
    private function _hideUtilitiesBadge(): void
    {
        // Hide the "Utilities" badge in the primary nav
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_TEMPLATE,
            static function () {
                // Hide the "Utilities" badge via CSS
                $css = 'li#nav-utilities a .badge {display:none !important}';
                Craft::$app->getView()->registerCss($css);
            }
        );
    }

    /**
     * Hide the "All Entries" link.
     */
    private function _hideAllEntries(): void
    {
        Event::on(
            Entry::class,
            Entry::EVENT_REGISTER_SOURCES,
            static function(RegisterElementSourcesEvent $event) {
                if ($event->context === 'index') {

                    // Remove "All Entries"
                    foreach ($event->sources as $i => $source) {
                        if (array_key_exists('key', $source) && ('*' === $source['key'])) {
                            unset($event->sources[$i]);
                        }
                    }

                }
            }
        );
    }

    /**
     * Show the element totals.
     */
    private function _showElementTotals(): void
    {
        // Show entry totals
        Event::on(
            Entry::class,
            Entry::EVENT_REGISTER_SOURCES,
            static function(RegisterElementSourcesEvent $event) {
                if ($event->context === 'index') {
                    foreach ($event->sources as $i => &$source) {

                        // If heading, skip
                        if (array_key_exists('heading', $source)) {
                            continue;
                        }

                        // If somehow criteria is missing, skip
                        if (!array_key_exists('criteria', $source)) {
                            continue;
                        }

                        // Get all matches, regardless of status
                        $criteria = $source['criteria'];
                        $criteria['status'] = null;

                        // Get total based on criteria
                        $query = Entry::find();
                        Craft::configure($query, $criteria);
                        $total = $query->count();

                        // If no total, continue
                        if (0 === $total) {
                            continue;
                        }

                        // Set badge
                        $source['badgeCount'] = $total;
                    }
                }
            }
        );

        // Show category totals
        Event::on(
            Category::class,
            Category::EVENT_REGISTER_SOURCES,
            static function(RegisterElementSourcesEvent $event) {
                if ($event->context === 'index') {
                    foreach ($event->sources as $i => &$source) {

                        // If heading, skip
                        if (array_key_exists('heading', $source)) {
                            continue;
                        }

                        // If somehow criteria is missing, skip
                        if (!array_key_exists('criteria', $source)) {
                            continue;
                        }

                        // Get all matches, regardless of status
                        $source['criteria']['status'] = null;

                        // Get total based on criteria
                        $query = Category::find();
                        Craft::configure($query, $source['criteria']);
                        $total = $query->count();

                        // If no total, continue
                        if (0 === $total) {
                            continue;
                        }

                        // Set badge
                        $source['badgeCount'] = $total;
                    }
                }
            }
        );

        // Show user totals
        Event::on(
            User::class,
            User::EVENT_REGISTER_SOURCES,
            static function(RegisterElementSourcesEvent $event) {
                if ($event->context === 'index') {
                    foreach ($event->sources as $i => &$source) {

                        // If heading, skip
                        if (array_key_exists('heading', $source)) {
                            continue;
                        }

                        // If somehow criteria is missing, skip
                        if (!array_key_exists('criteria', $source)) {
                            continue;
                        }

                        // Get all matches, regardless of status
                        $source['criteria']['status'] = null;

                        // Get total based on criteria
                        $query = User::find();
                        Craft::configure($query, $source['criteria']);
                        $total = $query->count();

                        // If no total, continue
                        if (0 === $total) {
                            continue;
                        }

                        // Set badge
                        $source['badgeCount'] = $total;
                    }
                }
            }
        );
    }

}
