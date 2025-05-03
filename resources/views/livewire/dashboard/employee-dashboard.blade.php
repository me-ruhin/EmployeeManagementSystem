<div>
    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Attendance Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Today's Attendance</h3>
            
            <div class="flex flex-col items-center">
                @if ($checkedInToday && !$checkedOutToday)
                    <div class="text-green-500 text-2xl mb-2">
                        <i class="fas fa-check-circle"></i> Checked In
                    </div>
                    <p class="text-gray-600 mb-3">
                        {{ $todayAttendance->check_in_time->format('h:i A') }}
                    </p>
                    <button wire:click="checkOut" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded">
                        Check Out
                    </button>
                @elseif ($checkedInToday && $checkedOutToday)
                    <div class="text-blue-500 text-2xl mb-2">
                        <i class="fas fa-calendar-check"></i> Day Complete
                    </div>
                    <p class="text-gray-600">
                        In: {{ $todayAttendance->check_in_time->format('h:i A') }} <br>
                        Out: {{ $todayAttendance->check_out_time->format('h:i A') }}
                    </p>
                @else
                    <div class="text-yellow-500 text-2xl mb-2">
                        <i class="fas fa-exclamation-circle"></i> Not Checked In
                    </div>
                    <button wire:click="checkIn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        Check In
                    </button>
                @endif
            </div>
        </div>
        
        <!-- Task Summary Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Task Summary</h3>
            
            <div class="flex justify-around">
                <div class="text-center">
                    <span class="block text-3xl font-bold text-blue-500">{{ $pendingTasks }}</span>
                    <span class="text-gray-600">Pending</span>
                </div>
                <div class="text-center">
                    <span class="block text-3xl font-bold text-green-500">{{ $completedTasks }}</span>
                    <span class="text-gray-600">Completed</span>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="{{ route('tasks.index') }}" class="text-blue-500 hover:underline block text-center">
                    View All Tasks
                </a>
            </div>
        </div>
        
        <!-- Leave Balance Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Leave Balance</h3>
            
            @if ($leaveBalance)
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Annual</span>
                        <span class="font-semibold">{{ $leaveBalance['annual'] }} days</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Sick</span>
                        <span class="font-semibold">{{ $leaveBalance['sick'] }} days</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Casual</span>
                        <span class="font-semibold">{{ $leaveBalance['casual'] }} days</span>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('leaves.create') }}" class="text-blue-500 hover:underline block text-center">
                        Apply for Leave
                    </a>
                </div>
            @else
                <p class="text-gray-500">No leave balance information available.</p>
            @endif
        </div>
        
        <!-- Quick Links Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
            
            <div class="space-y-2">
                <a href="{{ route('attendance.check-in-out') }}" class="block p-2 hover:bg-gray-100 rounded">
                    <i class="fas fa-clock text-blue-500 mr-2"></i> Attendance
                </a>
                <a href="{{ route('leaves.index') }}" class="block p-2 hover:bg-gray-100 rounded">
                    <i class="fas fa-calendar-alt text-green-500 mr-2"></i> Leaves
                </a>
                <a href="{{ route('tasks.index') }}" class="block p-2 hover:bg-gray-100 rounded">
                    <i class="fas fa-tasks text-orange-500 mr-2"></i> Tasks
                </a>
                <a href="{{ route('projects.index') }}" class="block p-2 hover:bg-gray-100 rounded">
                    <i class="fas fa-project-diagram text-purple-500 mr-2"></i> Projects
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Deadlines -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Upcoming Deadlines</h3>
            
            @if($upcomingDeadlines && $upcomingDeadlines->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($upcomingDeadlines as $task)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $task->title }}</div>
                                        <div class="text-sm text-gray-500">{{ $task->project->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm {{ $task->deadline->isPast() ? 'text-red-600' : 'text-gray-600' }}">
                                            {{ $task->deadline->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($task->priority == 'High') bg-red-100 text-red-800
                                            @elseif($task->priority == 'Medium') bg-yellow-100 text-yellow-800
                                            @elseif($task->priority == 'Critical') bg-purple-100 text-purple-800
                                            @else bg-green-100 text-green-800 @endif">
                                            {{ $task->priority }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500">No upcoming deadlines.</p>
            @endif
        </div>
        
        <!-- Recent Leave Applications -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Recent Leave Applications</h3>
            
            @if($recentLeaves && $recentLeaves->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentLeaves as $leave)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-medium text-gray-900">{{ $leave->leave_type }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600">
                                            {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
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
                </div>
            @else
                <p class="text-gray-500">No recent leave applications.</p>
            @endif
            
            <div class="mt-4 text-center">
                <a href="{{ route('leaves.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Apply for Leave
                </a>
            </div>
        </div>
    </div>
</div>
