

    <tr>
        <td class="icon-file-text-o"></td>
        <td><span class="name">   <a title="<?= $invoice->description; ?>"
                                     href="<?= url("app/fatura/{$invoice->id}"); ?>">
                <?= str_limit_words($invoice->description, 1, "&nbsp;<span class='icon-info icon-notext tooltip'><span class='tooltiptext'>{$invoice->description}</span></span>"); ?>
            </a></span>
            <?php
            $now = new DateTime();
            $due = new DateTime($invoice->due_at);
            $expire = $now->diff($due);
            $s = ($expire->days == 1 ? "" : "s");

            if (!$expire->days && $expire->invert):?>
                <span class="doc" style="color: var(--warning);">Hoje</span>
            <?php elseif (!$expire->invert): ?>
                <span class="doc">Em <?= ($expire->days <= 1 ? "1 dia" : "{$expire->days} dias") ?></span>
            <?php else: ?>
                <span class="doc"
                      style="color: var(--danger);">Há <?= ($expire->days <= 1 ? "1 dia" : "{$expire->days} dias"); ?></span>
            <?php endif; ?>
            </td>
        <td>1234566655</td>
        <td><?= str_price($invoice->value); ?></td>
        <td>
            <?php if ($invoice->status == 'unpaid'): ?>
                <span class="check <?= $invoice->type; ?> icon-circle-thin transition"
                      data-toggleclass="active icon-circle-thin icon-check-circle"
                      data-onpaid="<?= url("/app/onpaid"); ?>"
                      data-invoice="<?= $invoice->id; ?>"></span>
            <?php else: ?>
                <span class="check <?= $invoice->type; ?> icon-thumbs-o-up transition"
                      data-toggleclass="active icon-circle-thin icon-check-circle"
                      data-onpaid="<?= url("/app/onpaid"); ?>"
                      data-invoice="<?= $invoice->id; ?>"></span>
            <?php endif; ?>
    </tr>



