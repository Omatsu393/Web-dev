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
    const vehicleSearchInput = document.querySelector('#vehicle_search');
    const vehicleSearchStatus = document.querySelector('#vehicle-search-status');
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

    const vehicleSearchEntries = vehicleCatalog.flatMap((make) => make.models.flatMap((model) => model.variants.map((variant) => ({
        make,
        model,
        variant,
        label: `${make.name} ${model.name} ${variant.name}（${variant.drive_system}）`,
    }))));

    const clearVehicleSearch = () => {
        vehicleSearchInput.value = '';
        vehicleSearchStatus.textContent = '';
    };

    const updateVehicleSearchFromSelection = () => {
        const variant = selectedVariant();

        if (!variant) {
            return;
        }

        const entry = vehicleSearchEntries.find((item) => item.variant.id === variant.id);

        if (entry) {
            vehicleSearchInput.value = entry.label;
        }
    };

    const applyVehicleSearch = () => {
        const searchValue = vehicleSearchInput.value.trim().toLocaleLowerCase('ja');
        const entry = vehicleSearchEntries.find((item) => item.label.toLocaleLowerCase('ja') === searchValue);

        if (!entry) {
            vehicleSearchStatus.textContent = searchValue === '' ? '' : '候補から車種を選択してください。';
            return;
        }

        makeSelect.value = String(entry.make.id);
        populateModels(entry.model.id);
        populateVariants(entry.variant.id);
        applyVariant();
        vehicleSearchInput.value = entry.label;
        vehicleSearchStatus.textContent = `${entry.model.name} ${entry.variant.name}を設定しました。`;
    };

    makeSelect.addEventListener('change', () => {
        populateModels();
        populateVariants();
        clearVehicleDetails();
        clearVehicleSearch();
    });

    modelSelect.addEventListener('change', () => {
        populateVariants();
        clearVehicleDetails();
        clearVehicleSearch();
    });

    variantSelect.addEventListener('change', () => {
        applyVariant();
        updateVehicleSearchFromSelection();
    });

    vehicleSearchInput.addEventListener('input', applyVehicleSearch);
    vehicleSearchInput.addEventListener('change', applyVehicleSearch);

    populateModels(modelSelect.dataset.selected);
    populateVariants(variantSelect.dataset.selected);
    applyVariant(false);
    updateVehicleSearchFromSelection();

    const currentLocationButton = document.querySelector('#use-current-location');
    const currentLocationStatus = document.querySelector('#current-location-status');
    const originInput = document.querySelector('#origin');
    const originLatitude = document.querySelector('#origin_latitude');
    const originLongitude = document.querySelector('#origin_longitude');
    let locationOriginLabel = originLatitude.value && originLongitude.value ? originInput.value : '';

    const clearCurrentLocation = () => {
        originLatitude.value = '';
        originLongitude.value = '';
        locationOriginLabel = '';
    };

    const locationErrorMessage = (error) => {
        if (error.code === error.PERMISSION_DENIED) {
            return '位置情報が許可されませんでした。出発地を手入力してください。';
        }

        if (error.code === error.TIMEOUT) {
            return '現在地の取得がタイムアウトしました。もう一度試すか、手入力してください。';
        }

        return '現在地を取得できませんでした。出発地を手入力してください。';
    };

    if (!navigator.geolocation) {
        currentLocationButton.disabled = true;
        currentLocationStatus.textContent = 'このブラウザでは現在地取得を利用できません。出発地を手入力してください。';
    } else {
        currentLocationButton.addEventListener('click', () => {
            clearCurrentLocation();
            currentLocationButton.disabled = true;
            currentLocationStatus.textContent = '現在地を取得しています…';

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const latitude = position.coords.latitude.toFixed(6);
                    const longitude = position.coords.longitude.toFixed(6);

                    originLatitude.value = latitude;
                    originLongitude.value = longitude;
                    locationOriginLabel = `現在地（${latitude}, ${longitude}）`;
                    originInput.value = locationOriginLabel;
                    currentLocationStatus.textContent = '現在地を出発地に設定しました。';
                    currentLocationButton.disabled = false;
                },
                (error) => {
                    currentLocationStatus.textContent = locationErrorMessage(error);
                    currentLocationButton.disabled = false;
                    originInput.focus();
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000,
                },
            );
        });
    }

    originInput.addEventListener('input', () => {
        if (locationOriginLabel && originInput.value !== locationOriginLabel) {
            clearCurrentLocation();
            currentLocationStatus.textContent = '手入力の出発地を使用します。';
        }
    });
}
