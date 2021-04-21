{extends file="parent:frontend/detail/content.tpl"}

{block name='frontend_index_content_inner'}
    {$smarty.block.parent}

    <script type="text/javascript" src="https://webapp.easysize.me/web_app_v1.0/js/easysize.js"></script>
    <script>
        var product = {json_encode($sArticle)};
        var config = {json_encode($es_config)};
        var categories = {json_encode($sCategories)};
        var gender = '-';
        var stock = {literal}{}{/literal};
        var user = {json_encode($sESUserLoggedIn)};

        {literal}
        var categories_map = {};

        function parse_categories(categories, parent_id) {
            Object.keys(categories).forEach(function (category_id) {
                var category = categories[category_id];
                if (!categories_map.hasOwnProperty(category_id)) {
                    category.parent_id = parent_id || false;
                    category.map = [category.name];

                    if (category.parent_id) {
                        category.map = category.map.concat(categories_map[category.parent_id].map);
                    }

                    categories_map[category_id] = category;
                }

                parse_categories(category.subcategories, category_id);
            });
        }

        parse_categories(categories);
        var male_categories = config.male_categories.map(function(el) { return categories_map.hasOwnProperty(el) ? categories_map[el].name : ''; });
        var female_categories = config.female_categories.map(function(el) { return categories_map.hasOwnProperty(el) ? categories_map[el].name : ''; });
        var product_categories = categories_map[product.categoryID].map;

        product_categories.forEach(function(el) {
            if (male_categories.indexOf(el) !== -1) { gender = 'm'; }
            if (female_categories.indexOf(el) !== -1) { gender = 'f'; }
        });

        if (product.sConfigurator) {
            product.sConfigurator.forEach(function(el) {
                if (config.size_groups.indexOf(el.groupname) !== -1) {
                    Object.keys(el.values).forEach(function (index) {
                        stock[el.values[index].optionname] = 1;
                    });
                }
            });
        }

        function add_easysize_tracking_id() {
            if (EasySizeParametersDebug.easysize.pageview_id === -1) {
                setTimeout(add_easysize_tracking_id, 50);
            } else {
                var form = document.querySelector('form[data-add-article="true"]');
                var input = document.createElement('input');
                input.type = "hidden";
                // input.id = "esid-input";
                input.name = "_esid";
                input.value = EasySizeParametersDebug.easysize.pageview_id;
                form.append(input);
            }
        }

        var es_conf = {
            shop_id: config.shop_id,
            placeholder: config.placeholder,
            size_selector: config.size_selector,
            order_button_id: config.cart_button,
            product_brand: product.supplierName,
            product_type: product_categories.join(','),
            product_title: product.articleName,
            product_gender: gender,
            product_id: product.articleID,
            img_url: product.image.source,
            sizes_in_stock: stock,
            user_id: -1,
            loaded: add_easysize_tracking_id
        };
        {/literal}

        {$es_config['custom_js']}
    </script>
{/block}