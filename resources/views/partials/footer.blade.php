<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <img class="footer-logo" src="{{ asset('assets/img/logo/sov-summit_header_gold.webp') }}" alt="SOV SUMMIT" width="688" height="96" loading="lazy" />
        <p class="brandline">PEOPLE &middot; IDEAS &middot; IMPACT<br>Planning. Connecting. Delivering.</p>
        <p>SOV SUMMIT coordinates international events, management training, delegations, travel experiences, security support, and media coverage for organisations, corporations, institutions, and private clients.</p>
        @php
            $socialLabels = ['linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'x' => 'X', 'youtube' => 'YouTube'];
            $socialLinks = \App\Support\Seo::socialLinks();
        @endphp
        @if ($socialLinks !== [])
          <p class="footer-social">
            @foreach ($socialLinks as $network => $url)
              <a href="{{ $url }}" target="_blank" rel="noopener me">{{ $socialLabels[$network] ?? ucfirst($network) }}</a>@unless ($loop->last) &middot; @endunless
            @endforeach
          </p>
        @endif
      </div>
      <div class="footer-col">
        <h4>Site</h4>
        <ul>
          <li><a href="/about">About</a></li>
          <li><a href="/services">Services</a></li>
          <li><a href="/products">Solutions</a></li>
          <li><a href="/management-training">Management Training</a></li>
          <li><a href="/events">Events</a></li>
          <li><a href="/services/travel-experiences">Travel & Experiences</a></li>
          <li><a href="/blog">Insights</a></li>
          <li><a href="/faq">FAQ</a></li>
          <li><a href="/contact">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Services</h4>
        <ul>
          <li><a href="/services/planning-coordination">Planning &amp; Coordination</a></li>
          <li><a href="/services/conference-planning">Conference Planning</a></li>
          <li><a href="/services/delegation-management">Delegation Management</a></li>
          <li><a href="/services/security-coordination">Security Coordination</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <ul>
          <li>Sovereign Summit GmbH</li>
          <li>Bahnhofstrasse 21</li>
          <li>6300 Zug, Switzerland</li>
          <li><a href="mailto:info@sov-summit.com">info@sov-summit.com</a></li>
          <li><a href="https://wa.me/41715392300">+41 71 539 23 00</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <span id="year"></span> Sovereign Summit GmbH. All rights reserved.</span>
      <div class="legal-links">
        <a href="/legal-notice">Legal Notice</a>
        <a href="/privacy-policy">Privacy Policy</a>
        <a href="/cookie-policy">Cookie Policy</a>
        <a href="/terms-conditions">Terms &amp; Conditions</a>
      </div>
    </div>
  </div>
</footer>
