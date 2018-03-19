<?php

namespace Drupal\pwa\plugin\block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a button do enable/disable notifications
 *
 * @Block(
 *   id = "notification_toggle",
 *   admin_label = @Translation("Notification block"),
 *   category = @Translation("Notification"),
 * )
 */
class NotificationBlock extends BlockBase{

    /**
     * {@inheritdoc}
     */
    public function build() {
        return array(
          '#markup' => '<div id="pwa_notifications" class="button"> Enable notifications </div>'
        );
    }

}