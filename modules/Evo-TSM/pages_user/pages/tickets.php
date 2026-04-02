<div class="single-blog-details">
	<h1 class="post-title"><?= __('Evo-TSM/tss_view.user_header'); ?></h1> 
    <div class="text-end">
        <a href="<?= APP::getUrl("support") ?>/create" class="btn btn-sm" data-original-title="new"><i class="far fa-edit"></i> <?= __('Evo-TSM/tss_table.action_create'); ?></a>
    </div>
    <br>
    <div class="table-responsive">
        <table id="tickets_list" class="table table-sm">
            <thead>
                <tr class="table-dark">
                    <th scope="col"><?= __('Evo-TSM/tss_table.id'); ?></th>
                    <th scope="col"><?= __('Evo-TSM/tss_table.start_date'); ?></th>
                    <th scope="col"><?= __('Evo-TSM/tss_table.subject'); ?></th>
                    <th scope="col"><?= __('Evo-TSM/tss_table.state'); ?></th>
                    <th scope="col" class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($get_open AS $key) : ?>
                <tr data-id="<?= $key['id'] ?>">
                    <th scope="row"><?= $key['id'] ?></th>
                    <td><i class="far fa-calendar-alt"></i> <?= $key['create_date'] ?></td>
                    <td><a href="<?= APP::getURL('/support'); ?>/view&id=<?= $key['id'] ?>"><?= $key['subject'] ?></a></td>
                    <td><?= (empty($key['close_date'])) ? '<span class="badge bg-success" style="font-size: inherit;font-weight: 500;">'. __('Evo-TSM/tss_table.state_open') .'</span>' : '<span class="badge bg-danger" style="font-size: inherit;font-weight: 500;">'. __('Evo-TSM/tss_table.state_close') .'</span>'; ?></td>
                    <td class="text-end">
                        <ul class="nav justify-content-end">
                            <li class="nav-item">
                                <?= (empty($key['close_date'])) ? '' : '<button name="delete_ticket" class="btn btn-sm"><i class="far fa-trash-alt"></i></button>'; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>