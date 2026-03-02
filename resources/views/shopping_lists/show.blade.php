<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center sticky top-0 z-50 bg-white py-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Lista de {{ $shoppingList->creation_date->format('d/m') }}
                @if($shoppingList->market_name)
                    <span class="text-sm font-normal text-gray-500 ml-2">({{ $shoppingList->market_name }})</span>
                @endif
            </h2>
            <div class="text-lg font-bold text-green-600">
                R$ {{ number_format($shoppingList->total_value, 2, ',', '.') }}
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Formulário de Adicionar Item -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" x-data="productForm()">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">Adicionar Produto</h2>
                </header>

                <form method="post" action="{{ route('shopping-lists.items.store', $shoppingList) }}" class="mt-6 space-y-6">
                    @csrf
                    <input type="hidden" name="product_id" x-model="productId">
                    <input type="hidden" name="brand_id" x-model="brandId">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <x-input-label for="product_name" :value="__('Produto')" />
                            <x-text-input id="product_name" name="product_name" type="text" 
                                class="mt-1 block w-full" 
                                required autofocus placeholder="Ex: Arroz" 
                                x-model="productQuery"
                                @input="productId = ''"
                                @input.debounce.300ms="fetchProducts()"
                                autocomplete="off" />
                            <x-input-error :messages="$errors->get('product_name')" class="mt-2" />
                            
                            <!-- Product Results -->
                            <div x-show="showProductResults && products.length > 0" 
                                 @click.outside="showProductResults = false"
                                 class="absolute z-50 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto"
                                 style="display: none;">
                                <ul>
                                    <template x-for="product in products" :key="product.id">
                                        <li @click="selectProduct(product)" class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
                                            <span class="font-bold" x-text="product.name"></span>
                                            <span x-show="product.brand_name" class="text-gray-500 text-xs" x-text="'(' + product.brand_name + ')'"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <div class="relative">
                            <x-input-label for="brand_name" :value="__('Marca')" />
                            <x-text-input id="brand_name" name="brand_name" type="text" 
                                class="mt-1 block w-full" 
                                required placeholder="Ex: Camil" 
                                x-model="brandQuery"
                                @input="brandId = ''"
                                @input.debounce.300ms="fetchBrands()"
                                autocomplete="off" />
                            <x-input-error :messages="$errors->get('brand_name')" class="mt-2" />
                            
                            <!-- Brand Results -->
                            <div x-show="showBrandResults && brands.length > 0" 
                                 @click.outside="showBrandResults = false"
                                 class="absolute z-50 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto"
                                 style="display: none;">
                                <ul>
                                    <template x-for="brand in brands" :key="brand.id">
                                        <li @click="selectBrand(brand)" class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm" x-text="brand.name"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4 mb-4">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="is_per_kg" name="is_price_per_kg" value="1" x-model="isPerKg" @change="if(isPerKg) isPromo = false" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <label for="is_per_kg" class="text-sm font-medium text-gray-700">Preço por Kg</label>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="is_promo" x-model="isPromo" @change="if(isPromo) isPerKg = false" :disabled="isPerKg" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 disabled:opacity-50">
                            <label for="is_promo" class="text-sm font-medium text-gray-700" :class="{'text-gray-400': isPerKg}">Promoção / Atacado?</label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="quantity" x-text="isPerKg ? '{{ __('Peso (Kg)') }}' : '{{ __('Qtd') }}'" />
                            <x-text-input id="quantity" name="quantity" type="number" step="0.001" class="mt-1 block w-full" required min="0.001" value="1" />
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>
                        <div x-show="!isPromo">
                            <x-input-label for="unit_price" x-text="isPerKg ? '{{ __('Preço por Kg') }}' : '{{ __('Preço Unit.') }}'" />
                            <x-text-input id="unit_price" name="unit_price" type="number" step="0.01" class="mt-1 block w-full" placeholder="0.00" />
                            <x-input-error :messages="$errors->get('unit_price')" class="mt-2" />
                        </div>
                    </div>

                    <div x-show="isPromo" class="grid grid-cols-2 gap-4 bg-yellow-50 p-4 rounded-md border border-yellow-200">
                        <div>
                            <x-input-label for="promo_buy_quantity" :value="__('Leve Qtd')" />
                            <x-text-input id="promo_buy_quantity" name="promo_buy_quantity" type="number" class="mt-1 block w-full" min="2" placeholder="Ex: 6" x-model="promoQty" />
                        </div>
                        <div>
                            <x-input-label for="promo_price" :value="__('Pague Total')" />
                            <x-text-input id="promo_price" name="promo_price" type="number" step="0.01" class="mt-1 block w-full" placeholder="Ex: 10.00" x-model="promoPrice" />
                        </div>
                        <div class="col-span-2 text-xs text-gray-600" x-show="promoQty && promoPrice">
                           Custo por unidade na promoção: R$ <span x-text="(promoPrice / promoQty).toFixed(2)"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Adicionar') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <!-- Lista de Itens -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Itens no Carrinho</h3>
                    
                    @if($shoppingList->listItems->isEmpty())
                        <p class="text-gray-500">Nenhum item adicionado.</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach($shoppingList->listItems as $item)
                                <li class="py-4 flex justify-between items-center">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $item->product->name }}
                                            <span class="text-gray-500 font-normal">
                                                {{ $item->product->brand ? '- ' . $item->product->brand->name : '' }}
                                            </span>
                                            @if($item->product->is_price_per_kg)
                                                <span class="text-xs text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full ml-2">Kg</span>
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ floatval($item->quantity) }} {{ $item->product->is_price_per_kg ? 'kg' : 'un' }} x R$ {{ number_format($item->unit_price, 2, ',', '.') }}
                                            @if($item->promo_buy_quantity)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Promo: Leve {{ $item->promo_buy_quantity }} por {{ number_format($item->promo_price, 2, ',', '.') }}
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="text-base font-semibold text-gray-900">R$ {{ number_format($item->item_total, 2, ',', '.') }}</span>
                                        <form action="{{ route('list-items.destroy', $item) }}" method="POST" class="mt-1" onsubmit="return confirm('Remover item?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-900">Remover</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-gray-100 border border-gray-300 shadow-md sm:rounded-lg">
                <div class="flex justify-between items-center">
                    <form action="{{ route('shopping-lists.complete', $shoppingList) }}" method="POST" onsubmit="return confirm('Deseja finalizar esta lista? Ela será marcada como concluída.');">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-3 px-6 rounded-lg shadow-lg transform transition hover:scale-105 duration-150 ease-in-out">
                            Finalizar e Sair
                        </button>
                    </form>

                    <form action="{{ route('shopping-lists.destroy', $shoppingList) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta lista inteira?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-800 text-white font-bold py-3 px-6 rounded-lg shadow-lg transform transition hover:scale-105 duration-150 ease-in-out">
                            Excluir Lista
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        function productForm() {
            return {
                productQuery: '',
                productId: '',
                brandQuery: '',
                brandId: '',
                products: [],
                brands: [],
                showProductResults: false,
                showBrandResults: false,
                isPerKg: false,
                isPromo: false,
                promoQty: '',
                promoPrice: '',

                init() {
                    this.$watch('isPerKg', value => {
                        if (value) this.isPromo = false;
                    });
                    this.$watch('isPromo', value => {
                        if (value) this.isPerKg = false;
                    });
                },

                async fetchProducts() {
                    if (this.productQuery.length < 2) {
                        this.products = [];
                        this.showProductResults = false;
                        return;
                    }
                    try {
                        const response = await fetch(`{{ route('api.products.search') }}?query=${this.productQuery}`);
                        this.products = await response.json();
                        this.showProductResults = true;
                    } catch (error) {
                        console.error('Error fetching products:', error);
                    }
                },

                async fetchBrands() {
                    if (this.brandQuery.length < 2) {
                        this.brands = [];
                        this.showBrandResults = false;
                        return;
                    }
                    try {
                        const response = await fetch(`{{ route('api.brands.search') }}?query=${this.brandQuery}`);
                        this.brands = await response.json();
                        this.showBrandResults = true;
                    } catch (error) {
                        console.error('Error fetching brands:', error);
                    }
                },

                selectProduct(product) {
                    this.productId = product.id;
                    this.productQuery = product.name;
                    if (product.brand_name) {
                        this.brandQuery = product.brand_name;
                        this.brandId = product.brand_id || ''; // Assuming backend sends brand_id
                    }
                    this.isPerKg = product.is_price_per_kg;
                    this.showProductResults = false;
                },
                
                selectBrand(brand) {
                    this.brandId = brand.id;
                    this.brandQuery = brand.name;
                    this.showBrandResults = false;
                }
            }
        }
    </script>
</x-app-layout>
