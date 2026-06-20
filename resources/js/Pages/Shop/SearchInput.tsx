import React, { useState, useEffect, useRef } from 'react';
import { router } from '@inertiajs/react';
import { Search, TrendingUp } from 'lucide-react';
import api from '@/lib/api';

export function SearchInput({ initialValue = '' }: { initialValue?: string }) {
    const [query, setQuery] = useState(initialValue);
    const [suggestions, setSuggestions] = useState<string[]>([]);
    const [showSuggestions, setShowSuggestions] = useState(false);
    const wrapperRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (wrapperRef.current && !wrapperRef.current.contains(event.target as Node)) {
                setShowSuggestions(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    useEffect(() => {
        const fetchSuggestions = async () => {
            try {
                const res = await api.get(`/api/search-suggestions?q=${encodeURIComponent(query)}`);
                setSuggestions(res.data);
            } catch (e) {
                console.error(e);
            }
        };

        const timer = setTimeout(() => {
            fetchSuggestions();
        }, 300);

        return () => clearTimeout(timer);
    }, [query]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (query.trim()) {
            setShowSuggestions(false);
            router.get('/search', { q: query.trim() });
        }
    };

    const handleSuggestionClick = (suggestion: string) => {
        setQuery(suggestion);
        setShowSuggestions(false);
        router.get('/search', { q: suggestion });
    };

    return (
        <div ref={wrapperRef} className="relative w-full">
            <form onSubmit={handleSubmit} className="relative w-full z-10">
                <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <Search className="h-5 w-5 text-gray-400" />
                </div>
                <input
                    type="text"
                    value={query}
                    onChange={(e) => {
                        setQuery(e.target.value);
                        setShowSuggestions(true);
                    }}
                    onFocus={() => setShowSuggestions(true)}
                    className="block w-full pl-11 pr-4 py-4 border-2 border-gray-200 rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-gray-900 transition-colors bg-gray-50 text-lg font-medium relative z-10"
                    placeholder="Search for shirts, shoes, pants..."
                />
                <button
                    type="submit"
                    className="absolute inset-y-2 right-2 px-6 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-xl transition-colors z-20"
                >
                    Search
                </button>
            </form>

            {showSuggestions && suggestions.length > 0 && (
                <div className="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50">
                    <ul className="py-2">
                        {suggestions.map((suggestion, idx) => (
                            <li key={idx}>
                                <button
                                    type="button"
                                    onClick={() => handleSuggestionClick(suggestion)}
                                    className="w-full text-left px-5 py-3 hover:bg-gray-50 flex items-center gap-3 transition-colors"
                                >
                                    <TrendingUp className="w-4 h-4 text-gray-400" />
                                    <span className="font-medium text-gray-700">{suggestion}</span>
                                </button>
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}
