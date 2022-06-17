<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Price_range extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'price_range';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'abergan.ru';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Price range');
        $this->description = $this->l('Add a string showing quantity of product in chosen price range.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * 
     */
    public function install()
    {
        Configuration::updateValue('PRICE_RANGE_PRICE1', null);
        Configuration::updateValue('PRICE_RANGE_PRICE2', null);
        Configuration::updateValue('PRICE_RANGE_COUNT', null);

        return parent::install() &&
            $this->registerHook('displayFooter');
    }

    public function uninstall()
    {
        Configuration::deleteByName('PRICE_RANGE_PRICE1');
        Configuration::deleteByName('PRICE_RANGE_PRICE2');
        Configuration::deleteByName('PRICE_RANGE_COUNT');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitPrice_rangeModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPrice_rangeModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), 
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Цену указываете в рублях'),
                        'name' => 'PRICE_RANGE_PRICE1',
                        'label' => $this->l('цена от'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Цену указываете в рублях'),
                        'name' => 'PRICE_RANGE_PRICE2',
                        'label' => $this->l('цена до'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'PRICE_RANGE_PRICE1' => Configuration::get('PRICE_RANGE_PRICE1', null),
            'PRICE_RANGE_PRICE2' => Configuration::get('PRICE_RANGE_PRICE2', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }


    public function hookDisplayFooter()
    {
        $count = 0;
        // min price of our range
        $min = Configuration::get('PRICE_RANGE_PRICE1');
        // max price 
        $max = Configuration::get('PRICE_RANGE_PRICE2');

        // Get 100 items from DB. Quantity 100 is chosen in this example
        $products = Product::getProducts(Configuration::get('PS_LANG_DEFAULT'), 0, 100, 'id_product', 'asc');

        foreach ($products as $product){
            // Rounds a float 
            $price = round($product['price'], 2);
            // Adjust price at 20%
            $price = $price * 1.2;

            if ( $price >= $min && $price <= $max ) {
                $count += 1;
            }

        }
        // Assigne variables for Smarty
        $this->context->smarty->assign([
            'PRICE_RANGE_PRICE1' => Configuration::get('PRICE_RANGE_PRICE1'),
            'PRICE_RANGE_PRICE2' => Configuration::get('PRICE_RANGE_PRICE2'),
            'PRICE_RANGE_COUNT' => $count
        ]);

        return $this->display(__FILE__, 'price_range.tpl');
    }
}
