<div class="ticket-view">
    <div class="container">
        <div class="d-flex bd-highlight">
            <h3 class="p-2 flex-grow-1 bd-highlight">
                <a href="<?= APP::getURL('/support'); ?>" title="<?= __('Evo-TSM/tss_view.user_back'); ?>"><i class="fas fa-circle-arrow-left"></i></a>
                <?= $info["subject"]; ?> <span id="ETA" class="badge <?= (empty($info['close_date'])) ? 'bg-success' : 'bg-danger'; ?>"><?= (empty($info['close_date'])) ? __('Evo-TSM/tss_table.state_open') : __('Evo-TSM/tss_table.state_close'); ?></span>
            </h3>
            <div class="p-2 bd-highlight">
                <button class="btn" onclick="window.print()"><i class="fas fa-lg fa-print"></i></button>
            </div>
        </div>
        <blockquote>
            <?= $info["short_desc"]; ?>
        </blockquote>
        <div data-id="<?= $tid ?>" id="conversation" class="row">
                    
            <?php foreach($content AS $key => $val) : ?>

                <?php if($val["sid"] != 0) : ?>

                    <div class="col-sm-9" data-id="<?= $val["id"] ?>">
                        <h4 class="ticket_header"><?= __('Evo-TSM/tss_view.user_me'); ?> - <?= $val['send_date'] ?></h4>
                        <div class="ticket_content_client"><?= $val['msg'] ?></div>
                    </div>

                <?php else : ?>

                    <div class="col-lg-9 offset-sm-3" data-id="<?= $val["id"] ?>">
                        <div class="text-end">
                            <h4 class="ticket_header"><?= __('Evo-TSM/tss_view.user_staff'); ?> - <?= $val['send_date'] ?></h4>
                            <div class="ticket_content_staff"><?= $val['msg'] ?></div>
                        </div>
                    </div>

                <?php endif; ?>

            <?php endforeach; ?>

        </div>
        
        <div id="commentaire" class="col-md">

            <?php if($info['close_date'] === NULL) : ?>
                <form id="ticket_msg" action="" method="post">
                    <textarea id="editor" class="form-control" name="comment" placeholder="Message" maxlength="1024" rows="3" style="resize: none;margin-top:30px;"></textarea>
                    <div style="margin-top: 10px;">
                        <button class="btn btn-dark btn-block" name="ticket_comment" type="submit" ><?= __('Evo-TSM/tss_table.action_send'); ?></button>
                        <button class="btn btn-dark btn-block" name="ticket_close" type="submit" ><?= __('Evo-TSM/tss_table.action_close'); ?></button>
                    </div>
                </form>
            <?php endif; ?>
            
        </div>
    </div>
</div>