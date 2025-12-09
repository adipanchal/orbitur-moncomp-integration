<?php if (!defined('ABSPATH'))
   exit;
$lists = $lists ?? ['upcoming' => [], 'past' => []]; ?>

<div class="orbitur-bookings">
   <h3>Próximas</h3>
   <?php if (empty($lists['upcoming'])): ?>
      <p>Não há estadias próximas.</p>
   <?php else: ?>
      <ul>
         <?php foreach ($lists['upcoming'] as $b): ?>
            <li>
               <strong><?php echo esc_html($b['site'] ?? ''); ?></strong>
               — <?php echo esc_html(date_i18n('d/m/Y', strtotime($b['begin'] ?? ''))); ?>
               <?php if (!empty($b['url'])): ?> — <a href="<?php echo esc_url($b['url']); ?>" target="_blank"
                     rel="noopener">Gerir</a><?php endif; ?>
            </li>
         <?php endforeach; ?>
      </ul>
   <?php endif; ?>

   <h3>Anteriores</h3>
   <?php if (empty($lists['past'])): ?>
      <p>Não há estadias anteriores.</p>
   <?php else: ?>
      <ul>
         <?php foreach ($lists['past'] as $b): ?>
            <li>
               <strong><?php echo esc_html($b['site'] ?? ''); ?></strong>
               — <?php echo esc_html(date_i18n('d/m/Y', strtotime($b['begin'] ?? ''))); ?>
            </li>
         <?php endforeach; ?>
      </ul>
   <?php endif; ?>
</div>