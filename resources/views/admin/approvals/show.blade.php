<x-layouts.app :title="'Approval: ' . $request->title">
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $request->title }}</h1>
            <a href="{{ route('admin.approvals.index') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">← Back to approvals</a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Type:</span>
                    <span class="ml-2 text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Risk Level:</span>
                    @php
                        $riskColors = [
                            'low' => 'bg-green-100 text-green-800',
                            'medium' => 'bg-yellow-100 text-yellow-800',
                            'high' => 'bg-orange-100 text-orange-800',
                            'critical' => 'bg-red-100 text-red-800',
                        ];
                    @endphp
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $riskColors[$request->risk_level] ?? '' }}">
                        {{ ucfirst($request->risk_level) }}
                    </span>
                </div>
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Requested By:</span>
                    <span class="ml-2 text-gray-900 dark:text-white">{{ $request->requester?->name ?? 'Unknown' }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Date:</span>
                    <span class="ml-2 text-gray-900 dark:text-white">{{ $request->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Status:</span>
                    @php
                        $statusColors = [
                            'pending' => 'bg-amber-100 text-amber-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'expired' => 'bg-gray-100 text-gray-800',
                            'cancelled' => 'bg-gray-100 text-gray-800',
                        ];
                    @endphp
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$request->status] ?? '' }}">
                        {{ ucfirst($request->status) }}
                    </span>
                </div>
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Approvals:</span>
                    <span class="ml-2 text-gray-900 dark:text-white">{{ $request->current_approvals }} / {{ $request->required_approvals }}</span>
                </div>
            </div>

            @if($request->description)
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400 text-sm">Description:</span>
                    <p class="mt-1 text-gray-900 dark:text-white text-sm">{{ $request->description }}</p>
                </div>
            @endif

            @if($request->metadata)
                <div>
                    <span class="font-medium text-gray-500 dark:text-gray-400 text-sm">Details:</span>
                    <pre class="mt-1 p-3 bg-gray-50 dark:bg-gray-900 rounded text-xs text-gray-800 dark:text-gray-200 overflow-x-auto">{{ json_encode($request->metadata, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif

            @if($request->status === 'pending')
                <div class="flex space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    @if((int) $request->requester_id !== (int) auth()->id())
                        <form action="{{ route('admin.approvals.approve', $request) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">Approve</button>
                        </form>
                        <form action="{{ route('admin.approvals.reject', $request) }}" method="POST" class="flex space-x-2">
                            @csrf
                            <input type="text" name="comment" placeholder="Rejection reason (optional)" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 dark:text-white">
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium" onclick="return confirm('Reject this request?')">Reject</button>
                        </form>
                    @endif
                    @if((int) $request->requester_id === (int) auth()->id())
                        <form action="{{ route('admin.approvals.cancel', $request) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium" onclick="return confirm('Cancel this request?')">Cancel Request</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>

        @if($request->actions->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Approval History</h2>
                <div class="space-y-3">
                    @foreach($request->actions as $action)
                        <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <div class="flex-shrink-0">
                                @if($action->action === 'approve')
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600">✓</span>
                                @else
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-600">✗</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <span class="font-medium">{{ $action->approver?->name ?? 'Unknown' }}</span>
                                    {{ $action->action === 'approve' ? 'approved' : 'rejected' }} this request
                                </p>
                                @if($action->comment)
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $action->comment }}</p>
                                @endif
                                <p class="mt-1 text-xs text-gray-400">{{ $action->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
