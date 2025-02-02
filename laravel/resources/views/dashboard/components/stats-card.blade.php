@props(['title', 'value', 'color' => 'blue'])

<div class="bg-white overflow-hidden shadow rounded-lg transition duration-300 ease-in-out hover:shadow-lg transform hover:-translate-y-1">
    <div class="p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-full flex items-center justify-center bg-{{ $color }}-100 transition-colors duration-200 ease-in-out hover:bg-{{ $color }}-200">
                    <span class="text-{{ $color }}-600 text-xl font-bold">{{ $value }}</span>
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        {{ $title }}
                    </dt>
                    <dd class="flex items-baseline">
                        <div class="text-2xl font-semibold text-gray-900">
                            {{ $value }}
                        </div>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div> 