<x-filament-widgets::widget>
    <x-filament::section style="padding: 0 !important; overflow: hidden; border: 1px solid #e5e7eb; border-radius: 12px; background: #ffffff;">

        <style>
            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            .aging-row { border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 12px; background: #ffffff; overflow: hidden; }
            .aging-header { display: flex; justify-content: space-between; align-items: center; padding: 16px; cursor: pointer; user-select: none; }
            .aging-header:hover { background-color: #f8fafc; }
            .flex-align { display: flex; align-items: center; gap: 12px; }
            .aging-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
            .aging-table th { padding: 12px 16px; background: #f1f5f9; font-weight: 600; font-size: 12px; color: #475569; }
            .aging-table td { padding: 12px 16px; border-top: 1px solid #f1f5f9; }
        </style>

        <!-- Widget Header -->
        <div style="border-b: 1px solid #f1f5f9; padding: 20px; background: #ffffff; display: flex; align-items: center; gap: 12px;">
            <div style="padding: 8px; background: #f0fdf4; border-radius: 8px;">
                <svg style="width: 24px; height: 24px; color: #16a34a;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: #1e293b;">Receivables Aging Summary</h2>
                <p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;">Click any age category below to view detailed customer outstanding balances.</p>
            </div>
        </div>

        @php
            $data = $this->getAgingCalculations();
        @endphp

        <div x-data="{ activeSection: null }" style="padding: 20px; background: #f8fafc;">

            <!-- 0 to 30 Days Zone -->
            <div class="aging-row" style="border-color: #a7f3d0;">
                <div @click="activeSection = (activeSection === 'safe' ? null : 'safe')" class="aging-header">
                    <div class="flex-align">
                        {{-- <svg style="width: 5px; height: 5px; color: #059669; transition: transform 0.2s;" :style="activeSection === 'safe' ? 'transform: rotate(90deg);' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.0" d="M9 5l7 7-7 7" />
                        </svg> --}}
                        <div style="width: 200px">
                            <span style="font-weight: 700; font-size: 14px; color: #0f172a;">0 to 30 Days</span>
                            <span style="display: block; font-size: 10px; color: #64748b;" x-text="activeSection === 'safe' ? 'Click to hide details' : 'Click to view customers'"></span>
                        </div>
                    </div>
                    <div class="flex-align">
                        <span style="font-weight: 800; color: #059669; font-size: 16px;">PKR {{ number_format($data['safe']['total'] ?? 0, 2) }}</span>
                        <x-filament::badge color="success">Safe Zone</x-filament::badge>
                    </div>
                </div>

                <div x-show="activeSection === 'safe'" x-collapse style="display: none; border-top: 1px solid #e2e8f0; max-height: 280px; overflow-y: auto;" class="custom-scrollbar">
                    @if(count($data['safe']['items'] ?? []) > 0)
                        <table class="aging-table">
                            <thead>
                                <tr>
                                    <th>Customer Details</th>
                                    <th>Invoice Ref</th>
                                    <th style="text-align: right;">Amount Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['safe']['items'] as $item)
                                    <tr>
                                        <td style="font-weight: 700; color: #1e293b;">{{ $item['customer'] }}</td>
                                        <td style="color: #64748b;">#{{ $item['invoice_no'] }} <span style="font-size: 11px; color: #94a3b8;">({{ $item['days'] }} days ago)</span></td>
                                        <td style="text-align: right; font-weight: 700; color: #059669;">PKR {{ number_format($item['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p style="text-align: center; color: #64748b; font-size: 13px; padding: 20px; margin: 0;">No overdue invoices in this range.</p>
                    @endif
                </div>
            </div>

            <!-- 31 to 60 Days Zone -->
            <div class="aging-row" style="border-color: #fde68a;">
                <div @click="activeSection = (activeSection === 'reminder' ? null : 'reminder')" class="aging-header">
                    <div class="flex-align">
                        {{-- <svg style="width: 16px; height: 16px; color: #d97706; transition: transform 0.2s;" :style="activeSection === 'reminder' ? 'transform: rotate(90deg);' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg> --}}
                        <div>
                            <span style="font-weight: 700; font-size: 14px; color: #0f172a;">31 to 60 Days</span>
                            <span style="display: block; font-size: 10px; color: #64748b;" x-text="activeSection === 'reminder' ? 'Click to hide details' : 'Click to view customers'"></span>
                        </div>
                    </div>
                    <div class="flex-align">
                        <span style="font-weight: 800; color: #d97706; font-size: 16px;">PKR {{ number_format($data['reminder']['total'] ?? 0, 2) }}</span>
                        <x-filament::badge color="warning">Remind</x-filament::badge>
                    </div>
                </div>

                <div x-show="activeSection === 'reminder'" x-collapse style="display: none; border-top: 1px solid #e2e8f0; max-height: 280px; overflow-y: auto;" class="custom-scrollbar">
                    @if(count($data['reminder']['items'] ?? []) > 0)
                        <table class="aging-table">
                            <thead>
                                <tr>
                                    <th>Customer Details</th>
                                    <th>Invoice Ref</th>
                                    <th style="text-align: right;">Amount Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['reminder']['items'] as $item)
                                    <tr>
                                        <td style="font-weight: 700; color: #1e293b;">{{ $item['customer'] }}</td>
                                        <td style="color: #64748b;">#{{ $item['invoice_no'] }} <span style="font-size: 11px; color: #94a3b8;">({{ $item['days'] }} days ago)</span></td>
                                        <td style="text-align: right; font-weight: 700; color: #d97706;">PKR {{ number_format($item['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p style="text-align: center; color: #64748b; font-size: 13px; padding: 20px; margin: 0;">No overdue reminders right now.</p>
                    @endif
                </div>
            </div>

            <!-- 60+ Days Zone -->
            <div class="aging-row" style="border-color: #fca5a5;">
                <div @click="activeSection = (activeSection === 'danger' ? null : 'danger')" class="aging-header">
                    <div class="flex-align">
                        {{-- <svg style="width: 16px; height: 16px; color: #dc2626; transition: transform 0.2s;" :style="activeSection === 'danger' ? 'transform: rotate(90deg);' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg> --}}
                        <div>
                            <span style="font-weight: 700; font-size: 14px; color: #0f172a;">60+ Days Overdue</span>
                            <span style="display: block; font-size: 10px; color: #64748b;" x-text="activeSection === 'danger' ? 'Click to hide details' : 'Click to view customers'"></span>
                        </div>
                    </div>
                    <div class="flex-align">
                        <span style="font-weight: 800; color: #dc2626; font-size: 16px;">PKR {{ number_format($data['danger']['total'] ?? 0, 2) }}</span>
                        <x-filament::badge color="danger">Block / Danger</x-filament::badge>
                    </div>
                </div>

                <div x-show="activeSection === 'danger'" x-collapse style="display: none; border-top: 1px solid #e2e8f0; max-height: 280px; overflow-y: auto;" class="custom-scrollbar">
                    @if(count($data['danger']['items'] ?? []) > 0)

                     <table class="aging-table">
                                <thead>
                                    <tr>
                                        <th>Customer Details</th>
                                        <th>Invoice Ref</th>
                                        <th style="text-align: right;">Amount Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['danger']['items'] as $item)
                                        <tr>
                                            <td style="font-weight: 700; color: #1e293b;">{{ $item['customer'] }}</td>
                                            <td style="color: #64748b;">#{{ $item['invoice_no'] }} <span style="font-size: 11px; color: #94a3b8;">({{ $item['days'] }} days ago)</span></td>
                                            <td style="text-align: right; font-weight: 700; color: #dc2626;">PKR {{ number_format($item['amount'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p style="text-align: center; color: #64748b; font-size: 13px; padding: 20px; margin: 0;">No severe overdue invoices.</p>
                        @endif
                    </div>
                </div>

            </div>
        </x-filament::section>
    </x-filament-widgets::widget>