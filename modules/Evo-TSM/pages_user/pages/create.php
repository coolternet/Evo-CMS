<div class="single-blog-details">
    <h1 class="post-title"><?= __('Evo-TSM/tss_table.action_create'); ?></h1>
    <br>
    <form id="create-ticket" action="" method="post">
        <div class="form-group row">
            <label class="col-3 col-form-label text-end"><?= __('Evo-TSM/tss_create.subject'); ?></label>
            <div class="col-8">
                <input type="text" name="tc_subject" class="form-control" maxlength="64">
            </div>
        </div>
        <div class="form-group row">
            <label class="col-3 col-form-label text-end"><?= __('Evo-TSM/tss_create.description'); ?></label>
            <div class="col-8">
                <div id="commentaire">
                    <textarea class="form-control" name="tc_comment" placeholder="Message" maxlength="1024" rows="3" style="resize: none;"></textarea>
                </div>
            </div>
        </div>
        <div style="margin-top: 10px;" class="col-md">
            <button class="btn btn-dark btn-block" name="tcreate" type="submit" ><?= __('Evo-TSM/tss_table.action_create'); ?></button>
            <a href="<?= APP::getURL('/support'); ?>" class="btn btn-dark btn-block" ><?= __('Evo-TSM/tss_table.action_cancel'); ?></a>
        </div>
    </form>
</div>