@if($part === 'alerts')
    @if(($stats['low_stock_count'] ?? 0) > 0 || ($stats['expiring_count'] ?? 0) > 0 || ($stats['expired_count'] ?? 0) > 0)
        <div class="alert alert-warning border-warning border-opacity-25 d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 py-2 shadow-xs">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                <div>
                    <strong>Pharmacy Inventory Alerts:</strong>
                    @if(($stats['low_stock_count'] ?? 0) > 0)
                        <span class="badge badge-soft-amber me-1">{{ number_format($stats['low_stock_count']) }} item(s) below reorder level</span>
                    @endif
                    @if(($stats['expiring_count'] ?? 0) > 0)
                        <span class="badge badge-soft-red me-1">{{ number_format($stats['expiring_count']) }} batch(es) expiring in &le; 30 days</span>
                    @endif
                    @if(($stats['expired_count'] ?? 0) > 0)
                        <span class="badge bg-danger me-1">{{ number_format($stats['expired_count']) }} batch(es) already expired</span>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('inventory.expirations') }}" class="btn btn-warning btn-sm">Inspect Expirations</a>
                <a href="{{ route('inventory.reports') }}" class="btn btn-outline-dark btn-sm">Stock Reports</a>
            </div>
        </div>
    @endif
@elseif($part === 'communicable')
    @if(empty($charts['disease_classification']['top_communicable']))
        <tr>
            <td colspan="3" class="text-center text-muted py-4">
                <i class="bi bi-shield-check fs-3 text-success d-block mb-1"></i>
                No communicable illnesses recorded in registry.
            </td>
        </tr>
    @else
        @php $totalComm = max(1, $charts['disease_classification']['communicable_count']); @endphp
        @foreach($charts['disease_classification']['top_communicable'] as $comm)
            @php $pct = round(($comm['count'] / $totalComm) * 100, 1); @endphp
            <tr>
                <td class="fw-semibold text-dark">{{ $comm['name'] }}</td>
                <td class="text-end fw-bold">{{ number_format($comm['count']) }}</td>
                <td class="text-end">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <div class="progress flex-grow-1" style="height: 6px;">
                            <div class="progress-bar bg-danger" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="small text-muted" style="min-width: 38px;">{{ $pct }}%</span>
                    </div>
                </td>
            </tr>
        @endforeach
    @endif
@elseif($part === 'non_communicable')
    @if(empty($charts['disease_classification']['top_non_communicable']))
        <tr>
            <td colspan="3" class="text-center text-muted py-4">
                <i class="bi bi-shield-check fs-3 text-primary d-block mb-1"></i>
                No chronic conditions recorded in registry.
            </td>
        </tr>
    @else
        @php $totalNonComm = max(1, $charts['disease_classification']['non_communicable_count']); @endphp
        @foreach($charts['disease_classification']['top_non_communicable'] as $nonComm)
            @php $pct = round(($nonComm['count'] / $totalNonComm) * 100, 1); @endphp
            <tr>
                <td class="fw-semibold text-dark">{{ $nonComm['name'] }}</td>
                <td class="text-end fw-bold">{{ number_format($nonComm['count']) }}</td>
                <td class="text-end">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <div class="progress flex-grow-1" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="small text-muted" style="min-width: 38px;">{{ $pct }}%</span>
                    </div>
                </td>
            </tr>
        @endforeach
    @endif
@elseif($part === 'recent_visits')
    @forelse($recentVisits as $visit)
        <tr>
            <td>
                <a href="{{ $visit->detail_url }}" class="fw-bold font-monospace text-primary text-decoration-none">
                    {{ $visit->visit_number }}
                </a>
            </td>
            <td>
                <div class="fw-semibold text-dark">{{ $visit->patient?->full_name ?? '—' }}</div>
                <small class="text-muted">{{ ucfirst($visit->patient_classification) }}</small>
            </td>
            <td>
                <span class="badge {{ $visit->type_badge }}">{{ $visit->type_label }}</span>
            </td>
            <td class="small">{{ $visit->visit_date->format('M d, h:i A') }}</td>
            <td>
                <span class="badge badge-soft-gray">{{ $visit->disposition_label }}</span>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center text-muted py-4">No recent clinic visits recorded.</td>
        </tr>
    @endforelse
@elseif($part === 'recent_pe')
    @forelse($recentPhysicalExams as $pe)
        <tr>
            <td>
                <a href="{{ route('physical-examinations.show', $pe) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                    {{ $pe->pe_number }}
                </a>
            </td>
            <td>
                <div class="fw-semibold text-dark">{{ $pe->patient?->full_name ?? '—' }}</div>
                <small class="text-muted">{{ $pe->patient?->primary_identifier }}</small>
            </td>
            <td class="small">{{ $pe->exam_date->format('M d, Y') }}</td>
            <td>
                <span class="badge {{ $pe->fitness_badge_class }}">{{ $pe->fitness_assessment ?: 'Class A' }}</span>
            </td>
            <td>
                <span class="badge badge-soft-gray">{{ ucfirst($pe->status) }}</span>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center text-muted py-4">No physical exam assessments recorded yet.</td>
        </tr>
    @endforelse
@elseif($part === 'upcoming_schedules')
    @forelse($upcomingSchedules as $sched)
        <tr>
            <td>
                <a href="{{ route('physical-exam-schedules.show', $sched) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                    {{ $sched->schedule_number }}
                </a>
            </td>
            <td>
                <div class="fw-semibold text-dark">{{ $sched->title }}</div>
                <small class="text-muted">{{ ucfirst($sched->target_classification) }}</small>
            </td>
            <td>
                <div class="small fw-medium">{{ $sched->scheduled_date->format('M d, Y') }}</div>
                <small class="text-muted">{{ $sched->venue ?: 'Main Clinic' }}</small>
            </td>
            <td class="small">
                <span class="badge badge-soft-blue">{{ number_format($sched->attendees_count ?? 0) }} / {{ $sched->capacity ?: '∞' }}</span>
            </td>
            <td>
                <span class="badge {{ $sched->status === 'completed' ? 'badge-soft-green' : 'badge-soft-amber' }}">{{ ucfirst($sched->status) }}</span>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center text-muted py-4">No scheduled mass checkups pending.</td>
        </tr>
    @endforelse
@elseif($part === 'recent_dispensings')
    @forelse($recentDispensings as $disp)
        <tr>
            <td>
                <a href="{{ route('inventory.dispensings.show', $disp) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                    {{ $disp->dispensing_number }}
                </a>
            </td>
            <td>
                <div class="fw-semibold text-dark">{{ $disp->patient?->full_name ?? 'Walk-in' }}</div>
                <small class="text-muted">{{ $disp->items->count() }} item(s)</small>
            </td>
            <td>
                @foreach($disp->items->take(2) as $it)
                    <span class="badge badge-soft-gray me-1">{{ $it->medicine?->generic_name }} ({{ $it->quantity }})</span>
                @endforeach
                @if($disp->items->count() > 2)
                    <small class="text-muted">+{{ $disp->items->count() - 2 }} more</small>
                @endif
            </td>
            <td class="small">{{ $disp->dispensed_date?->format('M d, Y') ?? '—' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="text-center text-muted py-4">No recent dispensary records.</td>
        </tr>
    @endforelse
@elseif($part === 'recent_medical')
    @forelse($recentMedicalConsultations as $mc)
        <tr>
            <td>
                @if($mc->visit)
                    <a href="{{ route('medical-consultations.show', $mc->visit) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                        {{ $mc->visit->visit_number }}
                    </a>
                @else
                    —
                @endif
            </td>
            <td>
                <div class="fw-semibold text-dark">{{ $mc->visit?->patient?->full_name ?? '—' }}</div>
                <small class="text-muted">{{ $mc->visit?->classification_label ?? 'Client' }}</small>
            </td>
            <td class="fw-medium text-dark">{{ \Illuminate\Support\Str::limit($mc->diagnosis, 45) }}</td>
            <td>
                @if($mc->medical_certificate_issued)
                    <span class="badge badge-soft-green me-1"><i class="bi bi-file-earmark-medical me-1"></i>Med Cert ({{ $mc->rest_days ?? 1 }}d)</span>
                @endif
                @if($mc->referred_to)
                    <span class="badge badge-soft-amber"><i class="bi bi-Send me-1"></i>Referred</span>
                @endif
                @if(! $mc->medical_certificate_issued && ! $mc->referred_to)
                    <span class="badge badge-soft-gray">Outpatient Rx</span>
                @endif
            </td>
            <td class="small text-muted">{{ $mc->visit?->visit_date?->format('M d, Y') ?? '—' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center text-muted py-4">No medical consultations recorded.</td>
        </tr>
    @endforelse
@elseif($part === 'recent_dental')
    @forelse($recentDentalConsultations as $dc)
        <tr>
            <td>
                @if($dc->visit)
                    <a href="{{ route('dental-consultations.show', $dc->visit) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                        {{ $dc->visit->visit_number }}
                    </a>
                @else
                    —
                @endif
            </td>
            <td>
                <div class="fw-semibold text-dark">{{ $dc->visit?->patient?->full_name ?? '—' }}</div>
                <small class="text-muted">Hygiene: {{ $dc->oral_hygiene_status ?: 'Good' }}</small>
            </td>
            <td class="fw-medium text-dark">{{ \Illuminate\Support\Str::limit($dc->diagnosis, 42) }}</td>
            <td>
                <span class="badge badge-soft-blue">DMFT: {{ $dc->dmft_total }}</span>
                <span class="badge badge-soft-gray">D:{{ (int) $dc->dmft_decayed }} M:{{ (int) $dc->dmft_missing }} F:{{ (int) $dc->dmft_filled }}</span>
            </td>
            <td class="small text-muted">{{ $dc->visit?->visit_date?->format('M d, Y') ?? '—' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center text-muted py-4">No dental consultations recorded.</td>
        </tr>
    @endforelse
@elseif($part === 'recent_emergency')
    @forelse($recentEmergencyRecords as $er)
        <tr>
            <td>
                @if($er->visit)
                    <a href="{{ route('emergency-records.show', $er->visit) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                        {{ $er->incident_report_number ?: $er->visit->visit_number }}
                    </a>
                @else
                    —
                @endif
            </td>
            <td>
                <div class="fw-semibold text-dark">{{ $er->visit?->patient?->full_name ?? '—' }}</div>
                <small class="text-muted">{{ $er->incident_location ?: 'Campus' }}</small>
            </td>
            <td class="fw-medium text-dark">{{ $er->emergency_nature }}</td>
            <td>
                <span class="badge {{ $er->triage_badge }}">{{ strtoupper($er->triage_level) }}</span>
            </td>
            <td>
                <span class="badge badge-soft-gray">{{ $er->outcome ?: 'Stabilized' }}</span>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center text-muted py-4">No emergency triage cases recorded.</td>
        </tr>
    @endforelse
@elseif($part === 'recent_first_aid')
    @forelse($recentFirstAidRecords as $fa)
        <tr>
            <td>
                @if($fa->visit)
                    <a href="{{ route('first-aid-records.show', $fa->visit) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                        {{ $fa->visit->visit_number }}
                    </a>
                @else
                    —
                @endif
            </td>
            <td>
                <div class="fw-semibold text-dark">{{ $fa->visit?->patient?->full_name ?? '—' }}</div>
                <small class="text-muted">{{ $fa->incident_location ?: 'Campus' }}</small>
            </td>
            <td class="fw-medium text-dark">{{ $fa->injury_type }} ({{ $fa->body_part ?: 'General' }})</td>
            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($fa->supplies_used, 38) }}</td>
            <td class="small text-muted">{{ $fa->visit?->visit_date?->format('M d, Y') ?? '—' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center text-muted py-4">No first aid records logged.</td>
        </tr>
    @endforelse
@elseif($part === 'recent_audit')
    @forelse($recentActivity as $log)
        <tr>
            <td>
                <span class="badge bg-{{ $log->eventColor() }}-subtle text-{{ $log->eventColor() }} text-uppercase">
                    {{ $log->event }}
                </span>
            </td>
            <td class="fw-semibold text-dark">{{ $log->user?->name ?? 'System' }}</td>
            <td>{{ $log->description }}</td>
            <td class="font-monospace small text-muted">{{ $log->ip_address ?: '127.0.0.1' }}</td>
            <td class="small text-muted">{{ $log->created_at?->diffForHumans() ?? '—' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center text-muted py-4">No recent audit logs.</td>
        </tr>
    @endforelse
@endif

