
<h3 class="text-lg mb-3 font-bold p-3">
    {{ __('Form Components') }}
</h3>

<div class="space-y-6 mb-12">
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Text Inputs') }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Use the following component for text inputs:') }}
                    </p>
                </div>
                <div>
                    <button type="button"
                        class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        @click="showCode = !showCode">
                        <span x-show="!showCode">{{ __('Code') }}</span>
                        <span x-show="showCode">{{ __('Preview') }}</span>
                    </button>
                </div>
            </div>

            <div x-show="showCode">
                {!! ld_render_code_block(resource_path('views/demo/example-parts/x-inputs.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.example-parts.x-inputs')
            </div>
        </div>
    </div>


    <div class="border-b bg-gray-50 dark:bg-gray-800 p-4 mb-2">
        <h4 class="text-lg">{{ __('Input - Password') }}</h4>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            <code>&lt;x-inputs.password ... /&gt;</code>
        </p>
    </div>
    <div class="grid md:grid-cols-2 gap-4 mb-2">
        <x-inputs.password name="password" label="Password" :autogenerate="true" placeholder="Enter password" hint="Choose a strong password." required />
        <x-inputs.password name="confirm_password" label="Confirm Password" :autogenerate="true" placeholder="Re-enter password" required />
    </div>

    <div class="flex mb-0">
	    <x-inputs.file-input name="avatar" label="Profile Picture" hint="Upload your avatar." />
    </div>
    <div class="flex">
        <x-media-selector
            name="featured_image"
            label="{{ __('Featured Image') }}"
            :multiple="false"
            allowedTypes="images"
            removeCheckboxName="remove_featured_image"
            removeCheckboxLabel="{{ __('Remove featured image') }}"
            :showPreview="true"
            class="mt-1"
        />
    </div>

	{{-- Select --}}
	<x-inputs.select name="country" label="Country" :options="['bd' => 'Bangladesh', 'us' => 'USA', 'uk' => 'UK']" placeholder="Select country" hint="Choose your country." />

	{{-- Combobox --}}
	<x-inputs.combobox name="city" label="City" :options="['dhaka' => 'Dhaka', 'ny' => 'New York', 'london' => 'London']" placeholder="Select city" hint="Type to search city." />

	{{-- Checkbox --}}
	<x-inputs.checkbox name="terms" label="I agree to terms" hint="You must agree before submitting." />

	{{-- Radio --}}
	<div>
		<span class="form-label">Gender</span>
		<div class="flex gap-4">
			<x-inputs.radio name="gender" value="male" label="Male" />
			<x-inputs.radio name="gender" value="female" label="Female" />
			<x-inputs.radio name="gender" value="other" label="Other" />
		</div>
	</div>

	{{-- Textarea --}}
	<x-inputs.textarea name="bio" label="Bio" placeholder="Tell us about yourself" hint="Max 500 characters." rows="4" />

    {{-- Date Picker --}}
    <x-inputs.date-picker name="birthday" label="Birthday" placeholder="Select date" hint="Pick your birth date" />

	{{-- DateTime Picker --}}
	<x-inputs.datetime-picker name="dob" label="Date of Birth with time" hint="Select your birth date." />

	{{-- Range Input --}}
	<x-inputs.range-input name="experience" label="Experience (years)" min="0" max="30" step="1" hint="How many years of experience?" />

	{{-- Input Group --}}
	<x-inputs.input-group label="Twitter Handle" prepend="@" >
		<x-inputs.input name="twitter" placeholder="yourhandle" />
	</x-inputs.input-group>
</div>