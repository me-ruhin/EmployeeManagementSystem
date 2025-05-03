<div>
    <div class="mb-6">
        <h2 class="text-lg font-medium text-gray-900">Edit User: {{ $user->name }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            Update user information and settings.
        </p>
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

    <form wire:submit.prevent="save" class="bg-white shadow-sm rounded-md overflow-hidden">
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" wire:model="name" id="name" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" wire:model="email" id="email" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" wire:model="phone" id="phone" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select wire:model="role" id="role" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="Admin">Admin</option>
                        <option value="HR">HR</option>
                        <option value="Manager">Manager</option>
                        <option value="Department Head">Department Head</option>
                        <option value="Employee">Employee</option>
                    </select>
                    @error('role') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model="status" id="status" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Resigned">Resigned</option>
                        <option value="On Leave">On Leave</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Company -->
                <div>
                    <label for="company_id" class="block text-sm font-medium text-gray-700">Company</label>
                    <select wire:model="company_id" id="company_id" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Select Company</option>
                        @foreach($availableCompanies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('company_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Department -->
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                    <select wire:model="department_id" id="department_id" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ empty($availableDepartments) ? 'disabled' : '' }}>
                        <option value="">Select Department</option>
                        @foreach($availableDepartments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @if(empty($availableDepartments) && $company_id)
                        <span class="text-amber-500 text-xs mt-1">No departments available for selected company</span>
                    @elseif(empty($availableDepartments))
                        <span class="text-amber-500 text-xs mt-1">Please select a company first</span>
                    @endif
                    @error('department_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password (leave blank to keep current)</label>
                    <input type="password" wire:model="password" id="password" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="passwordConfirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" wire:model="passwordConfirmation" id="passwordConfirmation" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
            </div>

            <!-- Profile Photo -->
            <div>
                <label for="new_profile_photo" class="block text-sm font-medium text-gray-700">Profile Photo</label>
                <div class="mt-1 flex items-center">
                    <input type="file" wire:model="new_profile_photo" id="new_profile_photo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <div wire:loading wire:target="new_profile_photo" class="text-sm text-gray-500 mt-1">Uploading...</div>
                @error('new_profile_photo') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                
                <div class="mt-2 flex items-center">
                    @if ($new_profile_photo)
                        <img src="{{ $new_profile_photo->temporaryUrl() }}" alt="New Profile Photo" class="h-20 w-20 object-cover rounded-full mr-2">
                        <span class="text-xs text-gray-500">New photo preview</span>
                    @elseif ($profile_photo)
                        <img src="{{ Storage::url($profile_photo) }}" alt="Current Profile Photo" class="h-20 w-20 object-cover rounded-full mr-2">
                        <span class="text-xs text-gray-500">Current photo</span>
                    @else
                        <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                            <i class="fas fa-user text-gray-400 text-2xl"></i>
                        </div>
                        <span class="text-xs text-gray-500">No profile photo</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 text-right">
            <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-300 focus:outline-none focus:border-gray-300 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                Update User
            </button>
        </div>
    </form>
</div>
