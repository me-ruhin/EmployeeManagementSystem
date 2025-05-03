<div>
    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 uppercase">Total Employees</p>
                    <p class="text-2xl font-semibold">{{ $totalEmployees }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 uppercase">Departments</p>
                    <p class="text-2xl font-semibold">{{ $totalDepartments }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 uppercase">Active Projects</p>
                    <p class="text-2xl font-semibold">{{ $activeProjects }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600 mr-4">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 uppercase">Pending Leaves</p>
                    <p class="text-2xl font-semibold">{{ $pendingLeaves }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts and Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Attendance Chart -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Today's Attendance</h3>
            <div class="h-64">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
        
        <!-- Department Employee Count Chart -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Employees by Department</h3>
            <div class="h-64">
                <canvas id="departmentChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Projects and Leaves -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Projects Status -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Projects Status</h3>
            <div class="h-64">
                <canvas id="projectStatusChart"></canvas>
            </div>
        </div>
        
        <!-- Recent Leave Requests -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Recent Leave Requests</h3>
            @if($recentLeaves->count() > 0)
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Employee
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Duration
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLeaves as $leave)
                            <tr>
                                <td class="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                                    {{ $leave->employee->user->name }}
                                </td>
                                <td class="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                                    {{ $leave->leave_type }}
                                </td>
                                <td class="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                                    {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d') }}
                                </td>
                                <td class="px-5 py-2 border-b border-gray-200 bg-white text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold 
                                        @if($leave->status == 'Approved') bg-green-100 text-green-800
                                        @elseif($leave->status == 'Rejected') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ $leave->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500">No recent leave requests found.</p>
            @endif
        </div>
    </div>
    
    <!-- JavaScript for Charts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Attendance Chart
            const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
            const attendanceChart = new Chart(attendanceCtx, {
                type: 'pie',
                data: {
                    labels: ['Present', 'Absent', 'Late', 'On Leave'],
                    datasets: [{
                        data: [
                            {{ $todayAttendance['present'] }},
                            {{ $todayAttendance['absent'] }},
                            {{ $todayAttendance['late'] }},
                            {{ $todayAttendance['on_leave'] }}
                        ],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
            
            // Department Employee Count Chart
            const departmentLabels = @json($departmentEmployeeCount->pluck('name'));
            const departmentData = @json($departmentEmployeeCount->pluck('count'));
            
            const departmentCtx = document.getElementById('departmentChart').getContext('2d');
            const departmentChart = new Chart(departmentCtx, {
                type: 'bar',
                data: {
                    labels: departmentLabels,
                    datasets: [{
                        label: 'Number of Employees',
                        data: departmentData,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
            
            // Project Status Chart
            const projectCtx = document.getElementById('projectStatusChart').getContext('2d');
            const projectChart = new Chart(projectCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Ongoing', 'Completed', 'Cancelled'],
                    datasets: [{
                        data: [
                            {{ $projectsStatusCount['ongoing'] }},
                            {{ $projectsStatusCount['completed'] }},
                            {{ $projectsStatusCount['cancelled'] }}
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(255, 99, 132, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        });
    </script>
</div>
