@extends('adminlte::page')

@section('title', 'Database Migration | PrimeForecast')

@section('content_header')
    <h1><i class="fas fa-database"></i> Database Migration Manager</h1>
@stop

@section('content')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> <strong>คำเตือน:</strong> 
        กรุณา backup database ก่อนทำการ migrate ทุกครั้ง! 
        การ migrate อาจส่งผลกระทบต่อข้อมูลในระบบ
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Overview Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalMigrations }}</h3>
                    <p>Total Migrations</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-code"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $completedMigrations }}</h3>
                    <p>Completed</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pendingMigrations }}</h3>
                    <p>Pending</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-{{ $dbConnected ? 'success' : 'danger' }}">
                <div class="inner">
                    <h3><i class="fas fa-database"></i></h3>
                    <p>Database {{ $dbConnected ? 'Connected' : 'Error' }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-server"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-md-12">
            <button class="btn btn-primary" id="runAllBtn" {{ $pendingMigrations == 0 ? 'disabled' : '' }}>
                <i class="fas fa-play"></i> Run All Pending Migrations
            </button>
            <button class="btn btn-warning" id="rollbackBtn">
                <i class="fas fa-undo"></i> Rollback Last Batch
            </button>
            <button class="btn btn-info" id="refreshStatusBtn">
                <i class="fas fa-sync"></i> Refresh Status
            </button>
            <button class="btn btn-secondary" id="viewSchemaBtn">
                <i class="fas fa-table"></i> View Database Schema
            </button>
        </div>
    </div>

    <!-- Migrations Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Migration Files</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="migrationsTable" class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Migration File</th>
                            <th style="width: 80px;">Batch</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 180px;">Run Date</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($status as $index => $migration)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><small>{{ $migration['file'] }}</small></td>
                            <td class="text-center">{{ $migration['batch'] ?? '-' }}</td>
                            <td class="text-center">
                                @if($migration['status'] == 'completed')
                                    <span class="badge badge-success">Completed</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <small>{{ $migration['ran_at'] ? \Carbon\Carbon::parse($migration['ran_at'])->format('Y-m-d H:i:s') : '-' }}</small>
                            </td>
                            <td class="text-center">
                                @if($migration['status'] == 'pending')
                                    <button class="btn btn-xs btn-primary run-single-btn" data-migration="{{ $migration['file'] }}">
                                        <i class="fas fa-play"></i> Run
                                    </button>
                                @else
                                    <span class="text-muted"><i class="fas fa-check"></i> Done</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Logs Section -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-terminal"></i> Migration Output</h3>
            <div class="card-tools">
                <button class="btn btn-sm btn-tool" id="clearLogsBtn">
                    <i class="fas fa-trash"></i> Clear
                </button>
            </div>
        </div>
        <div class="card-body">
            <pre id="migrationOutput" style="max-height: 400px; overflow-y: auto; background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; font-size: 12px;">Waiting for migration commands...</pre>
        </div>
    </div>

    <!-- Schema Modal -->
    <div class="modal fade" id="schemaModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-table"></i> Database Schema</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="schemaContent">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin fa-3x"></i>
                            <p>Loading schema...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .small-box .icon {
        font-size: 70px;
    }
    #migrationOutput {
        font-family: 'Courier New', monospace;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .schema-table {
        margin-bottom: 30px;
    }
    .schema-table h4 {
        background: #007bff;
        color: white;
        padding: 10px;
        margin: 0;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Run All Pending Migrations
    $('#runAllBtn').click(function() {
        Swal.fire({
            title: 'Run All Pending Migrations?',
            html: '<p>This will execute all pending migrations.</p><p class="text-danger"><strong>Make sure you have a database backup!</strong></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, run migrations!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                runAllMigrations();
            }
        });
    });

    // Run Single Migration
    $('.run-single-btn').click(function() {
        const migration = $(this).data('migration');
        
        Swal.fire({
            title: 'Run This Migration?',
            text: migration,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, run it!'
        }).then((result) => {
            if (result.isConfirmed) {
                runSingleMigration(migration);
            }
        });
    });

    // Rollback
    $('#rollbackBtn').click(function() {
        Swal.fire({
            title: 'Rollback Last Batch?',
            html: '<p>This will rollback the last batch of migrations.</p><p class="text-danger"><strong>This action cannot be undone!</strong></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, rollback!',
            input: 'checkbox',
            inputPlaceholder: 'I understand the risks'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                rollbackMigrations();
            } else if (result.isConfirmed && !result.value) {
                Swal.fire('Error', 'Please confirm that you understand the risks', 'error');
            }
        });
    });

    // Refresh Status
    $('#refreshStatusBtn').click(function() {
        location.reload();
    });

    // View Schema
    $('#viewSchemaBtn').click(function() {
        $('#schemaModal').modal('show');
        loadSchema();
    });

    // Clear Logs
    $('#clearLogsBtn').click(function() {
        $('#migrationOutput').text('Logs cleared...');
    });

    // Functions
    function runAllMigrations() {
        const btn = $('#runAllBtn');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Running...');
        
        appendToOutput('=== Starting migration process ===\n');

        $.post('{{ route("admin.migration.run") }}', {})
            .done(function(response) {
                appendToOutput(response.output);
                appendToOutput('\n=== Migration completed successfully ===\n');
                
                Swal.fire({
                    title: 'Success!',
                    text: 'Migrations completed successfully',
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            })
            .fail(function(xhr) {
                const error = xhr.responseJSON?.error || 'Unknown error';
                appendToOutput('\n=== ERROR ===\n' + error + '\n');
                
                Swal.fire('Error', 'Migration failed: ' + error, 'error');
                btn.prop('disabled', false).html('<i class="fas fa-play"></i> Run All Pending Migrations');
            });
    }

    function runSingleMigration(migration) {
        appendToOutput('=== Running migration: ' + migration + ' ===\n');

        $.post('{{ route("admin.migration.run-single", ":migration") }}'.replace(':migration', migration), {})
            .done(function(response) {
                appendToOutput(response.output);
                appendToOutput('\n=== Migration completed ===\n');
                
                Swal.fire({
                    title: 'Success!',
                    text: 'Migration completed successfully',
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            })
            .fail(function(xhr) {
                const error = xhr.responseJSON?.error || 'Unknown error';
                appendToOutput('\n=== ERROR ===\n' + error + '\n');
                
                Swal.fire('Error', 'Migration failed: ' + error, 'error');
            });
    }

    function rollbackMigrations() {
        appendToOutput('=== Starting rollback process ===\n');

        $.post('{{ route("admin.migration.rollback") }}', { confirm: 1 })
            .done(function(response) {
                appendToOutput(response.output);
                appendToOutput('\n=== Rollback completed ===\n');
                
                Swal.fire({
                    title: 'Success!',
                    text: 'Rollback completed successfully',
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            })
            .fail(function(xhr) {
                const error = xhr.responseJSON?.error || 'Unknown error';
                appendToOutput('\n=== ERROR ===\n' + error + '\n');
                
                Swal.fire('Error', 'Rollback failed: ' + error, 'error');
            });
    }

    function loadSchema() {
        $.get('{{ route("admin.migration.schema") }}')
            .done(function(response) {
                let html = '';
                response.schema.forEach(function(table) {
                    html += '<div class="schema-table">';
                    html += '<h4>' + table.name + ' (' + table.row_count + ' rows)</h4>';
                    html += '<table class="table table-sm table-bordered">';
                    html += '<thead><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr></thead>';
                    html += '<tbody>';
                    table.columns.forEach(function(col) {
                        html += '<tr>';
                        html += '<td><strong>' + col.Field + '</strong></td>';
                        html += '<td>' + col.Type + '</td>';
                        html += '<td>' + col.Null + '</td>';
                        html += '<td>' + (col.Key || '-') + '</td>';
                        html += '<td>' + (col.Default || '-') + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                });
                $('#schemaContent').html(html);
            })
            .fail(function() {
                $('#schemaContent').html('<div class="alert alert-danger">Failed to load schema</div>');
            });
    }

    function appendToOutput(text) {
        const output = $('#migrationOutput');
        const currentText = output.text();
        
        if (currentText === 'Waiting for migration commands...') {
            output.text(text);
        } else {
            output.text(currentText + text);
        }
        
        // Auto scroll to bottom
        output.scrollTop(output[0].scrollHeight);
    }
});
</script>
@stop
