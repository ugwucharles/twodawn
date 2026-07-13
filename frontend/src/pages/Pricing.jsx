import { Link } from 'react-router-dom';
import { Check, ArrowRight, Zap, Shield, Headphones } from 'lucide-react';

function Pricing() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
      {/* Header */}
      <div className="bg-white border-b border-gray-100">
        <div className="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
          <Link to="/" className="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
            2DAWN
          </Link>
          <Link
            to="/register"
            className="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-2.5 rounded-full font-semibold hover:shadow-lg transition-all"
          >
            Get Started
            <ArrowRight className="w-4 h-4" />
          </Link>
        </div>
      </div>

      {/* Hero Section */}
      <div className="max-w-6xl mx-auto px-4 py-16 text-center">
        <h1 className="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
          Simple, Transparent Pricing
        </h1>
        <p className="text-xl text-gray-600 max-w-2xl mx-auto">
          Focus on creating amazing events. We handle the rest with a simple fee structure.
        </p>
      </div>

      {/* Pricing Card */}
      <div className="max-w-4xl mx-auto px-4 pb-16">
        <div className="bg-white rounded-3xl shadow-2xl border border-purple-100 overflow-hidden">
          <div className="bg-gradient-to-r from-purple-600 to-blue-600 px-8 py-6 text-white">
            <h2 className="text-2xl font-bold">Platform Fee</h2>
            <p className="text-purple-100 mt-1">Per ticket sold</p>
          </div>
          
          <div className="p-8">
            <div className="text-center mb-8">
              <div className="text-5xl font-bold text-gray-900 mb-2">
                10% + ₦100
              </div>
              <p className="text-gray-600">of ticket price</p>
            </div>

            <div className="bg-purple-50 rounded-2xl p-6 mb-8">
              <h3 className="font-semibold text-gray-900 mb-4">Fee Examples</h3>
              <div className="space-y-3">
                <div className="flex justify-between items-center py-2 border-b border-purple-200">
                  <span className="text-gray-700">₦1,000 ticket</span>
                  <span className="font-semibold text-gray-900">₦200 fee</span>
                </div>
                <div className="flex justify-between items-center py-2 border-b border-purple-200">
                  <span className="text-gray-700">₦5,000 ticket</span>
                  <span className="font-semibold text-gray-900">₦600 fee</span>
                </div>
                <div className="flex justify-between items-center py-2">
                  <span className="text-gray-700">₦10,000 ticket</span>
                  <span className="font-semibold text-gray-900">₦1,100 fee</span>
                </div>
              </div>
            </div>

            <div className="space-y-4 mb-8">
              <h3 className="font-semibold text-gray-900 mb-4">How It Works</h3>
              <div className="flex items-start gap-3">
                <Check className="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" />
                <p className="text-gray-700">
                  <strong>Pass fee to buyers:</strong> Add the 10% + ₦100 fee to your ticket price. Customers pay the full amount.
                </p>
              </div>
              <div className="flex items-start gap-3">
                <Check className="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" />
                <p className="text-gray-700">
                  <strong>Pay from your earnings:</strong> Deduct the fee from your payout. You receive the ticket price minus the fee.
                </p>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
              <div className="bg-gray-50 rounded-xl p-4 text-center">
                <Zap className="w-8 h-8 text-purple-600 mx-auto mb-2" />
                <h4 className="font-semibold text-gray-900 text-sm">Instant Setup</h4>
                <p className="text-xs text-gray-600 mt-1">Create events in minutes</p>
              </div>
              <div className="bg-gray-50 rounded-xl p-4 text-center">
                <Shield className="w-8 h-8 text-purple-600 mx-auto mb-2" />
                <h4 className="font-semibold text-gray-900 text-sm">Secure Payments</h4>
                <p className="text-xs text-gray-600 mt-1">Protected transactions</p>
              </div>
              <div className="bg-gray-50 rounded-xl p-4 text-center">
                <Headphones className="w-8 h-8 text-purple-600 mx-auto mb-2" />
                <h4 className="font-semibold text-gray-900 text-sm">24/7 Support</h4>
                <p className="text-xs text-gray-600 mt-1">Always here to help</p>
              </div>
            </div>

            <div className="text-center">
              <Link
                to="/register"
                className="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white px-8 py-3 rounded-full font-semibold hover:shadow-lg transition-all text-lg"
              >
                Start Selling Tickets
                <ArrowRight className="w-5 h-5" />
              </Link>
              <p className="text-sm text-gray-500 mt-3">No hidden fees. No monthly costs.</p>
            </div>
          </div>
        </div>
      </div>

      {/* FAQ Section */}
      <div className="max-w-4xl mx-auto px-4 pb-16">
        <h2 className="text-2xl font-bold text-gray-900 mb-6 text-center">Frequently Asked Questions</h2>
        <div className="space-y-4">
          <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h3 className="font-semibold text-gray-900 mb-2">Is there a monthly fee?</h3>
            <p className="text-gray-600">No! You only pay when you sell tickets. No monthly subscription or hidden costs.</p>
          </div>
          <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h3 className="font-semibold text-gray-900 mb-2">When do I get paid?</h3>
            <p className="text-gray-600">Payouts are processed automatically after your event ends. You can withdraw your earnings anytime.</p>
          </div>
          <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h3 className="font-semibold text-gray-900 mb-2">Can I offer free tickets?</h3>
            <p className="text-gray-600">Yes! Free events have no platform fee. You only pay when selling paid tickets.</p>
          </div>
          <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h3 className="font-semibold text-gray-900 mb-2">What payment methods do you accept?</h3>
            <p className="text-gray-600">We support card payments, bank transfers, and popular mobile money options for seamless transactions.</p>
          </div>
        </div>
      </div>

      {/* Footer CTA */}
      <div className="bg-gradient-to-r from-purple-600 to-blue-600 py-12">
        <div className="max-w-4xl mx-auto px-4 text-center">
          <h2 className="text-3xl font-bold text-white mb-4">Ready to sell tickets?</h2>
          <p className="text-purple-100 mb-6">Join thousands of event organizers on 2DAWN</p>
          <Link
            to="/register"
            className="inline-flex items-center gap-2 bg-white text-purple-600 px-8 py-3 rounded-full font-semibold hover:shadow-lg transition-all"
          >
            Create Free Account
            <ArrowRight className="w-5 h-5" />
          </Link>
        </div>
      </div>
    </div>
  );
}

export default Pricing;
