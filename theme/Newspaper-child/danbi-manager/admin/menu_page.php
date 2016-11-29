
<?php
function danbi_manager_items($items, $label) {
?>
    <form method="post" action="options.php">  
		<?php
		settings_fields('danbi_manager_license');
		?>
		<table class="wp-list-table widefat plugins">
			<thead>
				<tr>
					<th scope="row" class="check-column"></th>
					<th scope="col" id="name" class="manage-column column-name" style="width: 350px;"><?php echo $label; ?></th>
					<th scope="col" id="description" class="manage-column column-description" style="">라이센스</th>
				</tr>
			</thead>
			<tbody id="the-list">
				<?php
				if (empty($items)) : ?>
					<tr class="inactive">
						<td colspan="3">
							<?php if ($active_tab === 'plugins'): ?>
								현재 활성화된 플러그인 중 단비스토어에서 구입하신 플러그인이 없거나 업데이트를 지원하지 않는 플러그인이 설치되어 있습니다. 최신 버전으로 다시 설치해보십시오.
							<?php else: ?>
 								현재 활성화된 테마는 단비스토어에서 구입하신 테마가 아니거나 업데이트를 지원하지 않는 테마입니다. 최신 버전으로 다시 설치해보십시오.
 							<?php endif; ?>
						</td>
					</tr> 
				<?php
				else:
					ksort($items);
					$license = danbi_manager()->get_license();

					foreach ($items as $name => $data) :
						$id = $data['ID'];
						if (isset($data['license'])):
							$key = $data['license'];
						?>
							<tr class="active">
								<th scope="row" class="check-column"></th>
								<td class="plugin-title" valign="middle">
									<b><?php echo $data['Name']; ?></b>
								</td>
								<td class="column-description desc">
									무료
								</td>
							</tr>
						<?php
						else:
							$key = isset($license[$id]) ? $license[$id]['key'] : false;
							$status = isset($license[$id]) ? $license[$id]['status'] : false;
							if ($status === 'valid'):
							?>
								<tr class="active">
									<th scope="row" class="check-column"></th>
									<td class="plugin-title" valign="middle">
										<b><?php echo $data['Name']; ?></b>
									</td>
									<td class="column-description desc">
										<input name="danbi_license[<?php echo $id; ?>]" type="text" class="edd_license_key regular-text" readonly
											value="<?php esc_attr_e( $key ); ?>" placeholder="라이센스키를 입력하십시오." />
										<a class="button-secondary edd_license_action" href="#" data-action="deactivate" data-name="<?php echo $data['Name']; ?>" data-id="<?php echo $id; ?>">비활성화</a>
										<img src="<?php echo Danbi_Manager::$PLUGIN_URL;?>img/loading.gif" />
									</td>
								</tr>
							<?php else: ?>
								<tr class="<?php echo $key ? 'active update' : 'inactive'; ?>">
									<th scope="row" class="check-column"></th>
									<td class="plugin-title" valign="middle">
										<?php echo $data['Name']; ?>
									</td>
									<td class="column-description desc">
										<input name="danbi_license[<?php echo $id; ?>]" type="text" class="edd_license_key regular-text"
											value="<?php esc_attr_e( $key ); ?>" placeholder="라이센스키를 입력하십시오." />
										<a class="button-secondary edd_license_action" href="#" data-action="activate" data-name="<?php echo $data['Name']; ?>" data-id="<?php echo $id; ?>">활성화</a>
										<img src="<?php echo Danbi_Manager::$PLUGIN_URL;?>img/loading.gif" />
									</td>
								</tr>
								<?php if ($key): ?>
									<tr class="plugin-update-tr active">
										<td colspan="3" class="plugin-update colspanchange">
											<div class="update-message invalid-message">라이센스키가 정확하지 않습니다. 다시 입력해주십시오.</div>
										</td>
									</tr>
								<?php endif;
							endif;
						endif;
					endforeach;
				endif; ?>
			</tbody>
		</table>
		<p>※ 목록에는 활성화된 <?php echo $label; ?>만 표시됩니다.</p>
    </form> 
	<br/>
<?php	
}
?>
<div class="wrap">  
    <div id="icon-themes" class="icon32"></div>  
    <h2>단비 매니저</h2>  
    <?php settings_errors(); ?>  

	<?php if ( isset($_GET['activate']) ) : ?>
		<div id="message" class="updated"><p>라이센스가 활성화되었습니다.</p></div>
	<?php elseif ( isset($_GET['deactivate']) ) : ?>
		<div id="message" class="updated"><p>라이센스가 비활성화되었습니다.</p></div>
	<?php endif; ?>

    <h2 class="nav-tab-wrapper">  
        <a href="?page=danbi_manager&tab=license" class="nav-tab <?php echo $active_tab == 'license' ? 'nav-tab-active' : ''; ?>">라이센스</a>  
        <!--
        <a href="?page=danbi_manager&tab=themes" class="nav-tab <?php echo $active_tab == 'themes' ? 'nav-tab-active' : ''; ?>">테마</a>  
        -->
    </h2>  
	<p>
	단비스토어에서 구입하신 플러그인, 테마 업데이트를 위해 라이센스키를 입력한 후 활성화 하십시오. 라이센스 키는 <b>단비스토어</a> &gt; 마이 스토어 &gt; <a href="<?php echo DANBISTORE_URL; ?>/my-account/purchase-history/" target="_blank">구매내역</a></b>에서 확인 가능합니다.
	</p>
	<?php
	settings_fields('danbi_manager_license');

	danbi_manager_items($this->plugins, '플러그인');
	danbi_manager_items($this->themes, '테마');
	?>
	
</div> 
