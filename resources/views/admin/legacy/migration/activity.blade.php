@if(config('wlcms.layout.mode') === 'embedded')
    @extends(config('wlcms.layout.host_layout', 'layouts.admin-layout'))
@else
    @extends('wlcms::admin.layout')
@endif

@section('title', 'Migration Activity')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Migration Activity</h1>
        <div class="btn-group">
            <a href="{{ route('wlcms.admin.legacy.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Legacy Dashboard
            </a>
        </div>
    </div>

    <!-- Connection Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-database"></i> Legacy Database Connection
                    </h5>
                    <div class="d-flex align-items-center">
                        @if($connectionStatus === 'success')
                            <span class="badge bg-success me-2">
                                <i class="fas fa-check-circle"></i> Connected
                            </span>
                            <span class="text-muted">Legacy database is accessible</span>
                        @elseif($connectionStatus === 'error')
                            <span class="badge bg-danger me-2">
                                <i class="fas fa-times-circle"></i> Error
                            </span>
                            <span class="text-muted">Unable to connect to legacy database</span>
                        @else
                            <span class="badge bg-warning me-2">
                                <i class="fas fa-question-circle"></i> Unknown
                            </span>
                            <span class="text-muted">Connection status unknown</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Migration Job Statistics -->
    @if($jobStats['total_jobs'] > 0 || count($activeJobs) > 0 || count($recentJobs) > 0)
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-primary mb-2">{{ number_format($jobStats['total_jobs']) }}</div>
                        <h6 class="text-muted mb-0">Total Jobs</h6>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-info mb-2">{{ number_format($jobStats['running_jobs']) }}</div>
                        <h6 class="text-muted mb-0">Running</h6>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-success mb-2">{{ number_format($jobStats['completed_jobs']) }}</div>
                        <h6 class="text-muted mb-0">Completed</h6>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-danger mb-2">{{ number_format($jobStats['failed_jobs']) }}</div>
                        <h6 class="text-muted mb-0">Failed</h6>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Migration Tracking Not Available -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Migration Job Tracking</strong><br>
                    Migration job tracking is not yet available. Run the package migrations to enable comprehensive job monitoring and statistics.
                    <br><small class="text-muted">Command: <code>php artisan migrate</code></small>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Active Migration Jobs -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="mb-0">
                        <i class="fas fa-cog fa-spin"></i> Active Migration Jobs
                        @if(count($activeJobs) > 0)
                            <span class="badge bg-info">{{ count($activeJobs) }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($activeJobs) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($activeJobs as $job)
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">{{ $job['type_display'] ?? 'Migration Job' }}</div>
                                        <small class="text-muted">ID: {{ $job['id'] }}</small>
                                        <div class="mt-1">
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar" 
                                                     role="progressbar" 
                                                     style="width: {{ $job['progress']['percentage'] ?? 0 }}%"
                                                     aria-valuenow="{{ $job['progress']['percentage'] ?? 0 }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                {{ $job['progress']['processed_items'] ?? 0 }}/{{ $job['progress']['total_items'] ?? 0 }} items
                                                ({{ number_format($job['progress']['percentage'] ?? 0, 1) }}%)
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary">{{ $job['status'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clock text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No active migration jobs</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Migration Jobs -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Recent Migration Jobs
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($recentJobs) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentJobs as $job)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold">{{ $job['type'] ?? 'Migration Job' }}</div>
                                        <small class="text-muted">
                                            Started: {{ \Carbon\Carbon::parse($job['started_at'])->diffForHumans() }}
                                            @if(isset($job['duration']))
                                                • Duration: {{ $job['duration'] }}
                                            @endif
                                        </small>
                                        @if(isset($job['progress']['total_items']) && $job['progress']['total_items'] > 0)
                                            <div>
                                                <small class="text-muted">
                                                    {{ number_format($job['progress']['successful_items'] ?? 0) }} items migrated
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <span class="badge {{ 
                                            $job['status'] === 'completed' ? 'bg-success' : 
                                            ($job['status'] === 'failed' ? 'bg-danger' : 
                                            ($job['status'] === 'running' ? 'bg-primary' : 'bg-secondary'))
                                        }}">
                                            {{ ucfirst($job['status']) }}
                                        </span>
                                        @if(($job['error_count'] ?? 0) > 0)
                                            <div>
                                                <small class="text-danger">
                                                    {{ $job['error_count'] }} errors
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No recent migration jobs</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Article Mappings -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-link"></i> Recent Article Mappings
                        </h5>
                        <a href="{{ route('wlcms.admin.legacy.mappings.index') }}" class="btn btn-outline-primary btn-sm">
                            View All Mappings
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($recentMappings) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Legacy Article ID</th>
                                        <th>CMS Content</th>
                                        <th>Status</th>
                                        <th>Last Sync</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentMappings as $mapping)
                                        <tr>
                                            <td>
                                                <span class="font-monospace">#{{ $mapping['legacy_article_id'] }}</span>
                                            </td>
                                            <td>{{ $mapping['content_title'] }}</td>
                                            <td>
                                                <span class="badge {{ $mapping['status'] === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ ucfirst($mapping['status']) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($mapping['last_sync'])
                                                    <small class="text-muted">{{ $mapping['last_sync'] }}</small>
                                                @else
                                                    <small class="text-muted">Never</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $mapping['created_at'] }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-link text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-2">No article mappings found</p>
                            <a href="{{ route('wlcms.admin.legacy.mappings.create') }}" class="btn btn-primary">
                                Create First Mapping
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('wlcms.admin.legacy.mappings.create') }}" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Create Mapping
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('wlcms.admin.legacy.mappings.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-list"></i> View Mappings
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-info w-100" onclick="refreshPage()">
                                <i class="fas fa-sync-alt"></i> Refresh Activity
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('wlcms.admin.legacy.migration.export') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-download"></i> Export Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshPage() {
    location.reload();
}

// Auto-refresh active jobs every 30 seconds if there are any
@if(count($activeJobs) > 0)
    setTimeout(function() {
        location.reload();
    }, 30000);
@endif
</script>
@endsection