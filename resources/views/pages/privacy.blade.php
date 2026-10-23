{{-- Plain-language notice drafted for review. Sheriff's counsel must approve it before launch. --}}
<x-layouts.site title="Privacy">
    <x-page-header title="Privacy notice" tag="Policies" no="POL" lead="Last updated 23 October 2026. Draft for Sheriff’s review." />
    <article class="wrap mt-12 md:mt-16">
        <div class="prose-copy measure text-lg text-ink-2">
            <p>{{ sheriff('name') }} uses the information you send through this website only to answer your request: preparing a proposal, sending approved compliance documents, servicing your alarm, considering you for a job, or replying to a message.</p>
            <h2 class="text-ink">What we collect</h2>
            <ul>
                <li>Contact details: name, organisation, job title, email and phone.</li>
                <li>Request details: site location, number of posts, alarm address and fault description, and any documents you upload.</li>
                <li>Job applications: region, experience, and the documents you upload (police clearance, ID, certificates, CV).</li>
                <li>For approved vendor-pack links: which document was downloaded, when, and from which IP address.</li>
            </ul>
            <h2 class="text-ink">How we protect it</h2>
            <p>Uploaded documents are stored outside the public website and can only be opened by signed-in staff whose role needs them: business development for proposals and vendor packs, human resources for applications. Compliance-document links are signed, expire, and can be withdrawn.</p>
            <h2 class="text-ink">How long we keep it</h2>
            <p>Proposal and vendor-pack records are kept for the life of the business relationship. Unsuccessful job applications and their documents are deleted after twelve months unless you ask us to keep them.</p>
            <h2 class="text-ink">Your choices</h2>
            <p>You can ask to see, correct or delete your information.@if (setting('email')) Email <a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a>@endif @if (setting('phone')) or call <a href="{{ tel_href(setting('phone')) }}">{{ format_phone(setting('phone')) }}</a>@endif.</p>
        </div>
    </article>
</x-layouts.site>
