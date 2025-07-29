<div class="space-y-4">
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Activity Metadata</h3>
        
        @if(empty($metadata))
            <p class="text-gray-500 dark:text-gray-400">No metadata available for this activity.</p>
        @else
            <div class="space-y-2">
                @foreach($metadata as $key => $value)
                    <div class="flex justify-between items-start">
                        <span class="font-medium text-gray-700 dark:text-gray-300 capitalize">
                            {{ str_replace('_', ' ', $key) }}:
                        </span>
                        <span class="text-gray-900 dark:text-gray-100 text-right max-w-xs break-words">
                            @if(is_array($value))
                                <pre class="text-xs bg-gray-100 dark:bg-gray-700 p-2 rounded">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                            @elseif(is_bool($value))
                                {{ $value ? 'Yes' : 'No' }}
                            @elseif($key === 'amount' && is_numeric($value))
                                ₦{{ number_format($value / 100, 2) }}
                            @elseif($key === 'timestamp' || str_contains($key, '_at'))
                                {{ \Carbon\Carbon::parse($value)->format('M j, Y g:i A') }}
                            @else
                                {{ $value }}
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
