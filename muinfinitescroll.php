<?php

if (!defined('_PS_VERSION_'))
	exit;

class MuInfiniteScroll extends Module {
	
	public function __construct()
	{
		$this->name = 'muinfinitescroll';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'vmulot';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6');
		$this->bootstrap = true;
		
		parent::__construct();
		
		$this->displayName = $this->l('Mu Infinite Scroll');
		$this->description = $this->l('Automatically loads the next page of products into the bottom of the initial page.');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}

	public function install()
	{
		Configuration::updateValue('MU_INF_SCROLL_LOADING_TXT',$this->l( 'Loading...' ));
		Configuration::updateValue('MU_INF_SCROLL_LOADING_END',$this->l( 'No more products...' ));
		Configuration::updateValue('MU_INF_SCROLL_LOADING_IMG',$this->_path.'views/img/ajax-loader.gif');
		Configuration::updateValue('MU_INF_SCROLL_NEXT_SELECTOR','.pagination_next > a');
		Configuration::updateValue('MU_INF_SCROLL_ITEM_SELECTOR','.product_list > li');
		Configuration::updateValue('MU_INF_SCROLL_CONTENT_SELECTOR','.product_list');
		Configuration::updateValue('MU_INF_SCROLL_NAV_SELECTOR','.content_sortPagiBar');

		if (!parent::install() OR !$this->registerHook('displayFooter') OR  !$this->registerHook('displayHeader'))
			return false;

		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('MU_INF_SCROLL_LOADING_TXT')
			|| !Configuration::deleteByName('MU_INF_SCROLL_LOADING_END')
			|| !Configuration::deleteByName('MU_INF_SCROLL_LOADING_IMG')
			|| !Configuration::deleteByName('MU_INF_SCROLL_NEXT_SELECTOR')
			|| !Configuration::deleteByName('MU_INF_SCROLL_ITEM_SELECTOR')
			|| !Configuration::deleteByName('MU_INF_SCROLL_CONTENT_SELECTOR')
			|| !Configuration::deleteByName('MU_INF_SCROLL_NAV_SELECTOR')
			|| !parent::uninstall())
			return false;
		return true;
	}
	
	public function getContent()
	{
		$output = '';
		if(Tools::isSubmit('submitMuInfiniteScroll'))
		{
			Configuration::updateValue('MU_INF_SCROLL_LOADING_TXT', Tools::getValue('MU_INF_SCROLL_LOADING_TXT'));
			Configuration::updateValue('MU_INF_SCROLL_LOADING_END', Tools::getValue('MU_INF_SCROLL_LOADING_END'));
			Configuration::updateValue('MU_INF_SCROLL_LOADING_IMG', Tools::getValue('MU_INF_SCROLL_LOADING_IMG'));
			Configuration::updateValue('MU_INF_SCROLL_NEXT_SELECTOR', Tools::getValue('MU_INF_SCROLL_NEXT_SELECTOR'));
			Configuration::updateValue('MU_INF_SCROLL_ITEM_SELECTOR', Tools::getValue('MU_INF_SCROLL_ITEM_SELECTOR'));
			Configuration::updateValue('MU_INF_SCROLL_CONTENT_SELECTOR', Tools::getValue('MU_INF_SCROLL_CONTENT_SELECTOR'));
			Configuration::updateValue('MU_INF_SCROLL_NAV_SELECTOR', Tools::getValue('MU_INF_SCROLL_NAV_SELECTOR'));
		}
			
			if (isset($errors) && count($errors))
				$output .= $this->displayError(implode('<br />', $errors));
			else
				$output .= $this->displayConfirmation($this->l('Your settings have been updated.')); 

		return $output.$this->renderForm();
	}

	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cog'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Content Selector'),
						'name' => 'MU_INF_SCROLL_CONTENT_SELECTOR',
						'class' => 'fixed-width-xl',
					),
					array(
						'type' => 'text',
						'label' => $this->l('Navigation Selector'),
						'name' => 'MU_INF_SCROLL_NAV_SELECTOR',
						'class' => 'fixed-width-xl',
					),
					array(
						'type' => 'text',
						'label' => $this->l('Next Selector'),
						'name' => 'MU_INF_SCROLL_NEXT_SELECTOR',
						'class' => 'fixed-width-xl',
					),
					array(
						'type' => 'text',
						'label' => $this->l('Item Selector'),
						'name' => 'MU_INF_SCROLL_ITEM_SELECTOR',
						'class' => 'fixed-width-xl',
					),
					array(
						'type' => 'textarea',
						'label' => $this->l('Loading message'),
						'name' => 'MU_INF_SCROLL_LOADING_TXT',
						'class' => 'fixed-width-xl',
					),
					array(
						'type' => 'textarea',
						'label' => $this->l('End message'),
						'name' => 'MU_INF_SCROLL_LOADING_END',
						'class' => 'fixed-width-xl',
					),
					array(
						'type' => 'text',
						'label' => $this->l('Loading img'),
						'name' => 'MU_INF_SCROLL_LOADING_IMG',
						'class' => 'fixed-width-xl',
					)
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			)
		);
		
		$helper = new HelperForm();
		$helper->show_toolbar = true;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitMuInfiniteScroll';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => array(
				'MU_INF_SCROLL_CONTENT_SELECTOR' => Tools::getValue('MU_INF_SCROLL_CONTENT_SELECTOR', Configuration::get('MU_INF_SCROLL_CONTENT_SELECTOR')),
				'MU_INF_SCROLL_NAV_SELECTOR' => Tools::getValue('MU_INF_SCROLL_NAV_SELECTOR', Configuration::get('MU_INF_SCROLL_NAV_SELECTOR')),
				'MU_INF_SCROLL_NEXT_SELECTOR' => Tools::getValue('MU_INF_SCROLL_NEXT_SELECTOR', Configuration::get('MU_INF_SCROLL_NEXT_SELECTOR')),
				'MU_INF_SCROLL_ITEM_SELECTOR' => Tools::getValue('MU_INF_SCROLL_ITEM_SELECTOR', Configuration::get('MU_INF_SCROLL_ITEM_SELECTOR')),
				'MU_INF_SCROLL_LOADING_TXT' => Tools::getValue('MU_INF_SCROLL_LOADING_TXT', Configuration::get('MU_INF_SCROLL_LOADING_TXT')),
				'MU_INF_SCROLL_LOADING_END' => Tools::getValue('MU_INF_SCROLL_LOADING_END', Configuration::get('MU_INF_SCROLL_LOADING_END')),
				'MU_INF_SCROLL_LOADING_IMG' => Tools::getValue('MU_INF_SCROLL_LOADING_IMG', Configuration::get('MU_INF_SCROLL_LOADING_IMG')),
				),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function hookDisplayHeader($params)
	{
		if(!$this->loadJs())
			return;
		
		$this->context->controller->addJS($this->_path.'views/js/jquery.infinitescroll.min.js');
	}
	
	public function hookDisplayFooter($params)
	{
		if(!$this->loadJs())
			return;
		
		$options = array(
			'loading' => array(
				'msgText' => Configuration::get('MU_INF_SCROLL_LOADING_TXT'),
				'finishedMsg' => Configuration::get('MU_INF_SCROLL_LOADING_END'),
				'img' => Configuration::get('MU_INF_SCROLL_LOADING_IMG')
			),
			'nextSelector' => Configuration::get('MU_INF_SCROLL_NEXT_SELECTOR'),
			'navSelector' => Configuration::get('MU_INF_SCROLL_NAV_SELECTOR'),
			'itemSelector' => Configuration::get('MU_INF_SCROLL_ITEM_SELECTOR'),
			'contentSelector' => Configuration::get('MU_INF_SCROLL_CONTENT_SELECTOR'),
			'debug' => false,
			'behavior' => '',
			'callback' => ''
		);

		$options = json_encode($options);
		
		$this->smarty->assign(array('options' => $options));
		return $this->display(__FILE__, 'views/templates/hook/footer.tpl');
	}

	public function loadJs()
	{
		$enabledControllers = array( 'best-sales', 'category', 'manufacturer', 'new-products', 'search', 'supplier');
		if(isset($this->context->controller->php_self)){
			if(in_array($this->context->controller->php_self, $enabledControllers))
				return true;
		}
		return false;
	}

}
?>