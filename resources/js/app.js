const comparisonForm = document.querySelector('[data-comparison-form]');

if (comparisonForm) {
    const catalogElement = document.querySelector('#vehicle-catalog-data');
    const makeSelect = document.querySelector('#vehicle_make_id');
    const modelSelect = document.querySelector('#vehicle_model_id');
    const variantSelect = document.querySelector('#vehicle_variant_id');
    const fuelEfficiencyInput = document.querySelector('#fuel_efficiency');
    const catalogFuelEfficiency = document.querySelector('#catalog-fuel-efficiency');
    const fuelTypeInput = document.querySelector('#fuel_type');
    const fuelTypeDisplay = document.querySelector('#fuel_type_display');
    const vehicleCatalog = JSON.parse(catalogElement?.textContent ?? '[]');

    const replaceOptions = (select, placeholder, items, selectedValue, labelFor) => {
        select.replaceChildren(new Option(placeholder, ''));

        items.forEach((item) => {
            select.add(new Option(labelFor(item), item.id, false, String(item.id) === String(selectedValue)));
        });

        select.disabled = items.length === 0;
    };

    const selectedMake = () => vehicleCatalog.find((make) => String(make.id) === makeSelect.value);
    const selectedModel = () => selectedMake()?.models.find((model) => String(model.id) === modelSelect.value);
    const selectedVariant = () => selectedModel()?.variants.find((variant) => String(variant.id) === variantSelect.value);

    const clearVehicleDetails = () => {
        fuelTypeInput.value = '';
        fuelTypeDisplay.value = '';
        catalogFuelEfficiency.textContent = '車種を選ぶとカタログ燃費を自動入力します。実燃費に合わせて変更できます。';
    };

    const applyVariant = (overwriteFuelEfficiency = true) => {
        const variant = selectedVariant();

        if (!variant) {
            clearVehicleDetails();
            return;
        }

        if (overwriteFuelEfficiency) {
            fuelEfficiencyInput.value = Number(variant.fuel_efficiency).toFixed(1);
        }

        fuelTypeInput.value = variant.fuel_type;
        fuelTypeDisplay.value = variant.fuel_type_label;
        catalogFuelEfficiency.textContent = `カタログ燃費 ${Number(variant.fuel_efficiency).toFixed(1)}km/L（手動で上書きできます）`;
    };

    const populateVariants = (selectedValue = '') => {
        const variants = selectedModel()?.variants ?? [];
        replaceOptions(
            variantSelect,
            variants.length > 0 ? '選択してください' : '車種を先に選択',
            variants,
            selectedValue,
            (variant) => `${variant.name}（${variant.drive_system}）`,
        );
    };

    const populateModels = (selectedValue = '') => {
        const models = selectedMake()?.models ?? [];
        replaceOptions(
            modelSelect,
            models.length > 0 ? '選択してください' : 'メーカーを先に選択',
            models,
            selectedValue,
            (model) => model.name,
        );
    };

    makeSelect.addEventListener('change', () => {
        populateModels();
        populateVariants();
        clearVehicleDetails();
    });

    modelSelect.addEventListener('change', () => {
        populateVariants();
        clearVehicleDetails();
    });

    variantSelect.addEventListener('change', () => applyVariant());

    populateModels(modelSelect.dataset.selected);
    populateVariants(variantSelect.dataset.selected);
    applyVariant(false);
}
