<div>
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-lg font-medium text-gray-900">Employee Profile</h2>
        <div>
            @can('update', $employee)
            <a href="{{ route('employees.edit', $employee) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <i class="fas fa-edit mr-2"></i> Edit Employee
            </a>
            @endcan
            <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 ml-2">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Profile Header -->
    <div class="bg-white shadow-sm rounded-md overflow-hidden mb-6">
        <div class="p-6">
            <div class="md:flex md:items-center">
                <div class="flex-shrink-0">
                    @if ($employee->user->profile_photo)
                        <img class="h-24 w-24 rounded-full" src="{{ Storage::url($employee->user->profile_photo) }}" alt="{{ $employee->user->name }}">
                    @else
                        <div class="h-24 w-24 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-gray-400 text-4xl"></i>
                        </div>
                    @endif
                </div>
                <div class="md:ml-6 mt-4 md:mt-0">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $employee->user->name }}</h1>
                    <p class="text-gray-500">{{ $employee->designation }}</p>
                    <div class="mt-2 flex items-center">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($employee->user->status === 'Active') bg-green-100 text-green-800 
                            @elseif($employee->user->status === 'Inactive') bg-red-100 text-red-800
                            @elseif($employee->user->status === 'Resigned') bg-gray-100 text-gray-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $employee->user->status }}
                        </span>
                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($employee->user->role === 'Admin') bg-purple-100 text-purple-800 
                            @elseif($employee->user->role === 'HR') bg-blue-100 text-blue-800 
                            @elseif($employee->user->role === 'Manager') bg-green-100 text-green-800 
                            @elseif($employee->user->role === 'Department Head') bg-indigo-100 text-indigo-800 
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $employee->user->role }}
                        </span>
                    </div>
                </div>
                <div class="md:ml-auto mt-4 md:mt-0 flex flex-col md:items-end">
                    <p class="text-sm text-gray-500">
                        <i class="fas fa-envelope mr-2"></i> {{ $employee->user->email }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-phone mr-2"></i> {{ $employee->user->phone }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-calendar mr-2"></i> Joined {{ $employee->joining_date->format('M d, Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="mb-6">
        <nav class="flex space-x-4">
            <button 
                wire:click="setActiveTab('profile')" 
                class="px-3 py-2 text-sm font-medium rounded-md {{ $activeTab === 'profile' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700' }}">
                <i class="fas fa-user mr-1"></i> Profile
            </button>
            <button 
                wire:click="setActiveTab('attendance')" 
                class="px-3 py-2 text-sm font-medium rounded-md {{ $activeTab === 'attendance' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700' }}">
                <i class="fas fa-calendar-check mr-1"></i> Attendance
            </button>
            <button 
                wire:click="setActiveTab('leaves')" 
                class="px-3 py-2 text-sm font-medium rounded-md {{ $activeTab === 'leaves' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700' }}">
                <i class="fas fa-calendar-minus mr-1"></i> Leaves
            </button>
            <button 
                wire:click="setActiveTab('tasks')" 
                class="px-3 py-2 text-sm font-medium rounded-md {{ $activeTab === 'tasks' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700' }}">
                <i class="fas fa-tasks mr-1"></i> Tasks
            </button>
            <button 
                wire:click="setActiveTab('evaluations')" 
                class="px-3 py-2 text-sm font-medium rounded-md {{ $activeTab === 'evaluations' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700' }}">
                <i class="fas fa-star mr-1"></i> Evaluations
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="bg-white shadow-sm rounded-md overflow-hidden">
        <!-- Profile Tab -->
        @if ($activeTab === 'profile')
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Department</p>
                        <p class="mt-1">{{ $employee->department->name ?? 'Not Assigned' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Company</p>
                        <p class="mt-1">{{ $employee->company->name ?? 'Not Assigned' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Designation</p>
                        <p class="mt-1">{{ $employee->designation }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Reporting Manager</p>
                        <p class="mt-1">{{ $employee->reportingManager->name ?? 'None' }}</p>
                    </div>
                </div>

                <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @can('viewSalary', $employee)
                    <div>
                        <p class="text-sm font-medium text-gray-500">Salary</p>
                        <p class="mt-1">${{ number_format($employee->salary, 2) }}</p>
                    </div>
                    @endcan
                    @can('viewBankDetails', $employee)
                    <div>
                        <p class="text-sm font-medium text-gray-500">Bank Account</p>
                        <p class="mt-1">{{ $employee->bank_account_number ?: 'Not Provided' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tax ID</p>
                        <p class="mt-1">{{ $employee->tax_id ?: 'Not Provided' }}</p>
                    </div>
                    @endcan
                </div>
            </div>
        
        <!-- Attendance Tab -->
        @elseif ($activeTab === 'attendance')
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Attendance History</h3>
                    <div class="flex items-center">
                        <label for="view-month" class="mr-2 text-sm font-medium text-gray-700">Month:</label>
                        <input type="month" wire:model="viewMonth" id="view-month" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($attendanceData as $record)
                                <tr class="{{ $record['is_weekend'] ? 'bg-gray-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $record['day'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $record['day_name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($record['status'])
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($record['status'] === 'Present') bg-green-100 text-green-800 
                                                @elseif($record['status'] === 'Absent') bg-red-100 text-red-800
                                                @elseif($record['status'] === 'Late') bg-yellow-100 text-yellow-800
                                                @elseif($record['status'] === 'On Leave') bg-blue-100 text-blue-800 
                                                @endif">
                                                {{ $record['status'] }}
                                            </span>
                                        @elseif ($record['is_weekend'])
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Weekend
                                            </span>
                                        @else
                                            <span class="text-gray-400">--</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $record['check_in'] ?? '--' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $record['check_out'] ?? '--' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $record['hours_worked'] > 0 ? number_format($record['hours_worked'], 2) : '--' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No attendance records found for this month.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        
        <!-- Leaves Tab -->
        @elseif ($activeTab === 'leaves')
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Leave History</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approver</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($leaveData as $leave)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $leave->leave_type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $leave->start_date->format('M d, Y') }} - {{ $leave->end_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $leave->days }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                        {{ $leave->reason }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($leave->status === 'Approved') bg-green-100 text-green-800 
                                            @elseif($leave->status === 'Rejected') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $leave->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $leave->approver->name ?? 'Pending' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No leave records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        
        <!-- Tasks Tab -->
        @elseif ($activeTab === 'tasks')
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Task Assignments</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($taskData as $task)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ $task->description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $task->project->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="{{ $task->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                                            {{ $task->deadline->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($task->priority === 'Low') bg-green-100 text-green-800 
                                            @elseif($task->priority === 'Medium') bg-yellow-100 text-yellow-800
                                            @elseif($task->priority === 'High') bg-orange-100 text-orange-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ $task->priority }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($task->status === 'Completed') bg-green-100 text-green-800 
                                            @elseif($task->status === 'In Progress') bg-blue-100 text-blue-800
                                            @elseif($task->status === 'Cancelled') bg-gray-100 text-gray-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $task->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No tasks assigned.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        
        <!-- Evaluations Tab -->
        @elseif ($activeTab === 'evaluations')
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Performance Evaluations</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Review Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviewer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($evaluations as $evaluation)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $evaluation->review_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $evaluation->reviewer->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">{{ $evaluation->rating }}/5</div>
                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($evaluation->rating >= 4) bg-green-100 text-green-800 
                                                @elseif($evaluation->rating >= 3) bg-blue-100 text-blue-800
                                                @elseif($evaluation->rating >= 2) bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ $evaluation->rating_text }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-md">
                                        {{ $evaluation->comments ?? 'No comments provided' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No performance evaluations found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
