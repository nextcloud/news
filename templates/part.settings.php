<fieldset class="personalblock">
        <legend><strong><?php p($l->t('Import / Export OPML')); ?></strong></legend>
        <input type="file" id="opml-upload" name="files[]" read-file/>
        <button title="<?php p($l->t('Import')); ?>" forward-click="{selector:'#opml-upload'}">
                <?php p($l->t('Import')); ?>
        </button>
        <a class="button"
                href="<?php print_unescaped(\OC_Helper::linkToRoute('news_export_opml')) ?>"
                title="<?php p($l->t('Export')); ?>"><?php p($l->t('Export')); ?></a>
</fieldset>
<fieldset class="personalblock">
        <legend><strong><?php p($l->t('Subscribelet')); ?></strong></legend>
        <p><?php print_unescaped($this->inc('part.subscribelet'));?>
        </p>
</fieldset>
