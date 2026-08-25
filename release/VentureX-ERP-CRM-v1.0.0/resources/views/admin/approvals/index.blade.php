<x-layouts.app :title="'Approvals'" :breadcrumbs="[['label' => 'Admin'], ['label' => 'Approvals']]">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-2xl font-bold text-ink-800">Approval Center</h1>
            @if($pendingCount > 0)
                <span class="badge-amber">{{ $pendingCount }} pending</span>
            @endif
        </div>

        <div class="flex space-x-1 border-b border-ink-200">
            @php
                $tabs = [
                    'pending' => 'Pending Approval',
                    'my-requests' => 'My Requests',
                    'all' => 'All Requests',
                ];
            @endphp
            @foreach($tabs as $key => $label)
                <a href="{{ route('admin.approvals.index', ['tab' => $key]) }}"
                   class="px-4 py-2 text-sm font-medium border-b-2 transition-colors
                          {{ $tab === $key
                              ? 'border-blue-500 text-blue-600'
                              : 'border-transparent text-ink-500 hover:text-ink-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if($requests->isEmpty())
            <div class="text-center py-12 text-ink-400">
                <svg class="mx-auto h-12 w-12 text-ink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-2 text-sm">No {{ $tab === 'pending' ? 'pending approvals' : 'requests' }} found.</p>
            </div>
        @else
            <div class="card">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Request</th>
                                <th>Requested By</th>
                                <th>Risk</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                                <tr>
                                    <td class="text-sm text-ink-800">
                                        {{ ucfirst(str_replace('_', ' ', $req->request_type)) }}
                                    </td>
                                    <td class="text-sm text-ink-800 max-w-xs truncate">
                                        {{ $req->title }}
                                    </td>
                                    <td class="text-sm text-ink-500">
                                        {{ $req->requester?->name ?? 'Unknown' }}
                                    </td>
                                    <td class="text-sm">
                                        @php
                                            $riskBadge = [
                                                'low' => 'badge-green',
                                                'medium' => 'badge-amber',
                                                'high' => 'badge-orange',
                                                'critical' => 'badge-red',
                                            ];
                                        @endphp
                                        <span class="{{ $riskBadge[$req->risk_level] ?? 'badge-green' }}">
                                            {{ ucfirst($req->risk_level) }}
                                        </span>
                                    </td>
                                    <td class="text-sm">
                                        @php
                                            $statusBadge = [
                                                'pending' => 'badge-amber',
                                                'approved' => 'badge-green',
                                                'rejected' => 'badge-red',
                                                'expired' => 'badge-gray',
                                                'cancelled' => 'badge-gray',
                                            ];
                                        @endphp
                                        <span class="{{ $statusBadge[$req->status] ?? 'badge-gray' }}">
                                            {{ ucfirst($req->status) }}
                                        </span>
                                    </td>
                                    <td class="text-sm text-ink-500">
                                        {{ $req->created_at->diffForHumans() }}
                                    </td>
                                    <td class="text-sm text-right space-x-2">
                                        <a href="{{ route('admin.approvals.show', $req) }}"
                                           class="inline-flex items-center px-3 py-2 text-sm rounded-lg text-navy-600 hover:text-navy-500">
                                            View
                                        </a>
                                        @if($req->status === 'pending' && (int) $req->requester_id !== (int) auth()->id())
                                            <form action="{{ route('admin.approvals.approve', $req) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-2 text-sm rounded-lg text-green-600 hover:text-green-800 hover:bg-green-50">
                                                    Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.approvals.reject', $req) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-2 text-sm rounded-lg text-red-600 hover:text-red-800 hover:bg-red-50"
                                                    onclick="return confirm('Reject this request?')">
                                                    Reject
                                                </button>
                                            </form>
                                        @endif
                                        @if($req->status === 'pending' && (int) $req->requester_id === (int) auth()->id())
                                            <form action="{{ route('admin.approvals.cancel', $req) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-2 text-sm rounded-lg text-ink-600 hover:text-ink-800 hover:bg-ink-50"
                                                    onclick="return confirm('Cancel this request?')">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-ink-200">
                    {{ $requests->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
