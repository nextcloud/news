<?php print_unescaped($this->inc('part.content.warnings')) ?>

<div id="app-shortcuts">
    <div>
        <table>
            <tr>
                <th><?php p($l->t('Keyboard shortcut')); ?></th>
                <th><?php p($l->t('Description')); ?></th>
            </tr>
            <tr>
                <td>n / j / <?php p($l->t('right')); ?></td>
                <td><?php p($l->t('Jump to next article')); ?></td>
            </tr>
            <tr>
                <td>p / k / <?php p($l->t('left')); ?></td>
                <td><?php p($l->t('Jump to previous article')); ?></td>
            </tr>
            <tr>
                <td>s / l</td>
                <td><?php p($l->t('Toggle star article')); ?></td>
            </tr>
            <tr>
                <td>h</td>
                <td>
                    <?php p($l->t('Star article and jump to next one')); ?>
                </td>
            </tr>
            <tr>
                <td>u</td>
                <td>
                    <?php p($l->t('Toggle keep current article unread')); ?>
                </td>
            </tr>
            <tr>
                <td>o</td>
                <td><?php p($l->t('Open article in new tab')); ?></td>
            </tr>
            <tr>
                <td>e</td>
                <td>
                    <?php p($l->t('Toggle expand article in compact view')); ?>
                </td>
            </tr>
            <tr>
                <td>r</td>
                <td><?php p($l->t('Refresh')); ?></td>
            </tr>
            <tr>
                <td>f</td>
                <td><?php p($l->t('Load next feed')); ?></td>
            </tr>
            <tr>
                <td>d</td>
                <td><?php p($l->t('Load previous feed')); ?></td>
            </tr>
            <tr>
                <td>c</td>
                <td><?php p($l->t('Load next folder')); ?></td>
            </tr>
            <tr>
                <td>v</td>
                <td><?php p($l->t('Load previous folder')); ?></td>
            </tr>
            <tr>
                <td>a</td>
                <td><?php p($l->t('Scroll to active navigation entry')); ?></td>
            </tr>
            <tr>
                <td>q</td>
                <td><?php p($l->t('Focus search field')); ?></td>
            </tr>
            <tr>
                <td>shift + a</td>
                <td><?php p($l->t('Mark current article\'s feed/folder read')); ?></td>
            </tr>
        </table>
    </div>
</div>