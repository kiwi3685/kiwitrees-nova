<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class sitemap_KT_Module extends KT_Module implements KT_Module_Config {
	const RECORDS_PER_VOLUME = 500;    // Keep sitemap files small, for memory, CPU and max_allowed_packet limits.
	const CACHE_LIFE         = 1209600; // Two weeks

	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module - see http://en.wikipedia.org/wiki/Sitemaps */ KT_I18N::translate('Sitemaps');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Sitemaps” module */ KT_I18N::translate('Generate sitemap files for search engines.');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin':
			$this->admin();
			break;
		case 'generate':
			Zend_Session::writeClose();
			$this->generate(KT_Filter::get('file'));
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	private function generate($file) {
		if ($file=='sitemap.xml') {
			$this->generate_index();
		} elseif (preg_match('/^sitemap-(\d+)-([isrmn])-(\d+).xml$/', $file, $match)) {
			$this->generate_file($match[1], $match[2], $match[3]);
		} else {
			header('HTTP/1.0 404 Not Found');
		}
	}

	// The index file contains references to all the other files.
	// These files are the same for visitors/users/admins.
	private function generate_index() {
		// Check the cache
		$timestamp=get_module_setting($this->getName(), 'sitemap.timestamp');
		if ($timestamp > KT_TIMESTAMP - self::CACHE_LIFE) {
			$data=get_module_setting($this->getName(), 'sitemap.xml');
		} else {
			$data='';
			$lastmod='<lastmod>'.date('Y-m-d').'</lastmod>';
			foreach (KT_Tree::getAll() as $tree) {
				if (get_gedcom_setting($tree->tree_id, 'include_in_sitemap')) {
					$n=KT_DB::prepare("SELECT COUNT(*) FROM `##individuals` WHERE i_file=?")->execute(array($tree->tree_id))->fetchOne();
					for ($i=0; $i<=$n/self::RECORDS_PER_VOLUME; ++$i) {
						$data.='<sitemap><loc>'. KT_SERVER_NAME . KT_SCRIPT_PATH.'module.php?mod='.$this->getName().'&amp;mod_action=generate&amp;file=sitemap-'.$tree->tree_id.'-i-'.$i.'.xml</loc>'.$lastmod.'</sitemap>'.PHP_EOL;
					}
					$n=KT_DB::prepare("SELECT COUNT(*) FROM `##sources` WHERE s_file=?")->execute(array($tree->tree_id))->fetchOne();
					if ($n) {
						for ($i=0; $i<=$n/self::RECORDS_PER_VOLUME; ++$i) {
							$data.='<sitemap><loc>'. KT_SERVER_NAME . KT_SCRIPT_PATH.'module.php?mod='.$this->getName().'&amp;mod_action=generate&amp;file=sitemap-'.$tree->tree_id.'-s-'.$i.'.xml</loc>'.$lastmod.'</sitemap>'.PHP_EOL;
						}
					}
					$n=KT_DB::prepare("SELECT COUNT(*) FROM `##other` WHERE o_file=? AND o_type='REPO'")->execute(array($tree->tree_id))->fetchOne();
					if ($n) {
						for ($i=0; $i<=$n/self::RECORDS_PER_VOLUME; ++$i) {
							$data.='<sitemap><loc>'. KT_SERVER_NAME . KT_SCRIPT_PATH.'module.php?mod='.$this->getName().'&amp;mod_action=generate&amp;file=sitemap-'.$tree->tree_id.'-r-'.$i.'.xml</loc>'.$lastmod.'</sitemap>'.PHP_EOL;
						}
					}
					$n=KT_DB::prepare("SELECT COUNT(*) FROM `##other` WHERE o_file=? AND o_type='NOTE'")->execute(array($tree->tree_id))->fetchOne();
					if ($n) {
						for ($i=0; $i<=$n/self::RECORDS_PER_VOLUME; ++$i) {
							$data.='<sitemap><loc>'. KT_SERVER_NAME . KT_SCRIPT_PATH.'module.php?mod='.$this->getName().'&amp;mod_action=generate&amp;file=sitemap-'.$tree->tree_id.'-n-'.$i.'.xml</loc>'.$lastmod.'</sitemap>'.PHP_EOL;
						}
					}
					$n=KT_DB::prepare("SELECT COUNT(*) FROM `##media` WHERE m_file=?")->execute(array($tree->tree_id))->fetchOne();
					if ($n) {
						for ($i=0; $i<=$n/self::RECORDS_PER_VOLUME; ++$i) {
							$data.='<sitemap><loc>'. KT_SERVER_NAME . KT_SCRIPT_PATH.'module.php?mod='.$this->getName().'&amp;mod_action=generate&amp;file=sitemap-'.$tree->tree_id.'-m-'.$i.'.xml</loc>'.$lastmod.'</sitemap>'.PHP_EOL;
						}
					}
				}
			}
			$data='<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'.PHP_EOL.'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.$data.'</sitemapindex>'.PHP_EOL;
			// Cache this data
			set_module_setting($this->getName(), 'sitemap.xml', $data);
			set_module_setting($this->getName(), 'sitemap.timestamp', KT_TIMESTAMP);
		}
		header('Content-Type: application/xml');
		header('Content-Length: '.strlen($data));
		echo $data;
	}

	// A separate file for each family tree and each record type.
	// These files depend on access levels, so only cache for visitors.
	private function generate_file($ged_id, $rec_type, $volume) {
		$tree = KT_Tree::get($ged_id);
		// Check the cache
		$timestamp = get_module_setting($this->getName(), 'sitemap-' . $ged_id . '-' . $rec_type . '-' . $volume . '.timestamp');
		if ($timestamp > KT_TIMESTAMP - self::CACHE_LIFE && !KT_USER_ID) {
			$data = get_module_setting($this->getName(), 'sitemap-' . $ged_id . '-' . $rec_type . '-' . $volume . '.xml');
		} else {
			$data = '<url><loc>' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'index.php?ged=' . $tree->tree_name_url . '</loc></url>' . PHP_EOL;
			$records = array();
			switch ($rec_type) {
			case 'i':
				$rows=KT_DB::prepare(
					"SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec".
					" FROM `##individuals`".
					" WHERE i_file=?".
					" ORDER BY i_id".
					" LIMIT " . self::RECORDS_PER_VOLUME . " OFFSET " . ($volume * self::RECORDS_PER_VOLUME)
				)->execute(array($ged_id))->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows as $row) {
					$records[] = KT_Person::getInstance($row);
				}
				break;
			case 's':
				$rows = KT_DB::prepare(
					"SELECT 'SOUR' AS type, s_id AS xref, s_file AS ged_id, s_gedcom AS gedrec".
					" FROM `##sources`".
					" WHERE s_file=?".
					" ORDER BY s_id".
					" LIMIT " . self::RECORDS_PER_VOLUME . " OFFSET " . ($volume * self::RECORDS_PER_VOLUME)
				)->execute(array($ged_id))->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows as $row) {
					$records[] = KT_Source::getInstance($row);
				}
				break;
			case 'r':
				$rows = KT_DB::prepare(
					"SELECT 'REPO' AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec".
					" FROM `##other`".
					" WHERE o_file=? AND o_type='REPO'".
					" ORDER BY o_id".
					" LIMIT " . self::RECORDS_PER_VOLUME . " OFFSET " . ($volume * self::RECORDS_PER_VOLUME)
				)->execute(array($ged_id))->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows as $row) {
					$records[] = KT_Repository::getInstance($row);
				}
				break;
			case 'n':
				$rows = KT_DB::prepare(
					"SELECT 'NOTE' AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec".
					" FROM `##other`".
					" WHERE o_file=? AND o_type='NOTE'".
					" ORDER BY o_id".
					" LIMIT " . self::RECORDS_PER_VOLUME . " OFFSET " . ($volume * self::RECORDS_PER_VOLUME)
				)->execute(array($ged_id))->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows as $row) {
					$records[] = KT_Note::getInstance($row);
				}
				break;
			case 'm':
				$rows = KT_DB::prepare(
					"SELECT 'OBJE' AS type, m_id AS xref, m_file AS ged_id, m_gedcom AS gedrec, m_titl, m_filename".
					" FROM `##media`".
					" WHERE m_file=?".
					" ORDER BY m_id".
					" LIMIT " . self::RECORDS_PER_VOLUME . " OFFSET " . ($volume * self::RECORDS_PER_VOLUME)
				)->execute(array($ged_id))->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows as $row) {
					$records[] = KT_Media::getInstance($row);
				}
				break;
			}
			foreach ($records as $record) {
				if ($record->canDisplayName()) {
					$data .= '<url>';
					$data .= '<loc>'. KT_SERVER_NAME . KT_SCRIPT_PATH.$record->getHtmlUrl() . '</loc>';
					$chan=$record->getChangeEvent();
					if ($chan) {
						$date = $chan->getDate();
						if ($date->isOK()) {
							$data .= '<lastmod>' . $date->minDate()->Format('%Y-%m-%d') . '</lastmod>';
						}
					}
					$data .= '</url>' . PHP_EOL;
				}
			}
			$data='<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . PHP_EOL . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL . $data . '</urlset>' . PHP_EOL;
			// Cache this data - but only for visitors, as we don’t want
			// visitors to see data created by logged-in users.
			if (!KT_USER_ID) {
				set_module_setting($this->getName(), 'sitemap-' . $ged_id . '-' . $rec_type . '-' . $volume . '.xml', $data);
				set_module_setting($this->getName(), 'sitemap-' . $ged_id . '-' . $rec_type . '-' . $volume . '.timestamp', KT_TIMESTAMP);
			}
		 }
		header('Content-Type: application/xml');
		header('Content-Length: ' . strlen($data));
		echo $data;
	}

	private function admin() {
		include KT_THEME_URL . 'templates/adminData.php';

		global $iconStyle;

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader();

		// Save the updated preferences
		if (KT_Filter::post('action', 'save')=='save') {
			foreach (KT_Tree::getAll() as $tree) {
				set_gedcom_setting($tree->tree_id, 'include_in_sitemap', KT_Filter::postBool('include'.$tree->tree_id));
			}
			// Clear cache and force files to be regenerated
			KT_DB::prepare(
				"DELETE FROM `##module_setting` WHERE setting_name LIKE 'sitemap%'"
			)->execute();
		}

		$include_any = false;

		echo relatedPages($moduleTools, $this->getConfigLink());

		echo pageStart('sitemap', $controller->getPageTitle()); ?>

			<div class="cell callout info-help">
				<?php echo /* I18N: The www.sitemaps.org site is translated into many languages (e.g. http://www.sitemaps.org/fr/) - choose an appropriate URL. */
				KT_I18N::translate('
					Sitemaps are a way for webmasters to tell search engines about the pages on a 
					website that are available for crawling.  
					All major search engines support sitemaps.  
					For more information, see <a href="http://www.sitemaps.org/">www.sitemaps.org</a>.
				'); ?>
			</div>

			<form class="cell" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin">
				<input type="hidden" name="action" value="save">

				<div class="grid-x grid-margin-x grid-margin-y">
					<div class="cell medium-4">
						<?php echo KT_I18N::translate('Which family trees should be included in the sitemaps?'); ?>
					</div>
					<div class="cell medium-8">				
						<?php foreach (KT_Tree::getAll() as $tree) { ?>
							<p>
								<input type="checkbox" name="include<?php echo $tree->tree_id; ?>"
									<?php if (get_gedcom_setting($tree->tree_id, 'include_in_sitemap')) { ?>
										 checked="checked"
										<?php $include_any = true;
									} ?>
								>
								<?php echo $tree->tree_title_html; ?>
							</p>
						<?php } ?>
					</div>
					<div class="cell">
						<?php singleButton(); ?>
					</div>
				</div>

			</form>

			<hr class="cell">

			<?php if ($include_any) {
				$site_map_url1 = KT_SERVER_NAME . KT_SCRIPT_PATH . 'module.php?mod=' . $this->getName() . '&amp;mod_action=generate&amp;file=sitemap.xml';
				$site_map_url2 = rawurlencode(KT_SERVER_NAME.KT_SCRIPT_PATH . 'module.php?mod=' . $this->getName() . '&mod_action=generate&file=sitemap.xml'); ?>

				<div class="cell callout info-help">
					<?php echo KT_I18N::translate('
						To tell search engines that sitemaps are available, 
						you should add the following line to your robots.txt file.
					'); ?>
					<pre>Sitemap: <?php echo $site_map_url1; ?></pre>
				</div>

				<div class="cell callout info-help">
					<?php echo KT_I18N::translate('
						Or you can also the following links to directly tell these major search engines that sitemaps are available.
					'); ?>
					<ul>
						<li>
							<a target="_new" href="https://www.bing.com/webmasters/about">
								Bing
								<span>
									&nbsp;-&nbsp;<?php echo KT_I18N::translate('Link to Bing webmaster tools. Login to submit your sitemap.'); ?>
								</span>
							</a>
						</li>
						<li>
							<a target="_new" href="https://www.google.com/webmasters/tools/ping?sitemap=<?php echo $site_map_url2; ?>">
								Google
								<span>
									&nbsp;-&nbsp;<?php echo KT_I18N::translate('Link to Google webmaster tools. Automatically submits your sitemap.'); ?>
								</span>
							</a>
						</li>
					</ul>
				</div>

			<?php }

		pageClose();

	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin';
	}
}
