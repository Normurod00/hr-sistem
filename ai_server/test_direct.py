# -*- coding: utf-8 -*-
"""
Direct testing of AI components without HTTP
"""

import sys
import os
import yaml
import json

# Add path to modules
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from core.document_parser import DocumentParser
from core.rule_based_analyzer import RuleBasedAnalyzer

# Load configuration
with open('config.yaml', 'r', encoding='utf-8') as f:
    config = yaml.safe_load(f)

print("="*60)
print("AI COMPONENTS TESTING (DIRECT)")
print("="*60)
print()

# Initialize components
doc_parser = DocumentParser(config.get('documents', {}))
hr_analyzer = RuleBasedAnalyzer(config.get('hr', {}))

print("[OK] Components initialized")
print()

# Test 1: Resume Parsing
print("1. RESUME PARSING")
print("-" * 60)

test_resume = """Ivanov Ivan Ivanovich
PHP Developer

Experience: 5 years

Skills:
- PHP 8.x, Laravel 10+
- MySQL, PostgreSQL
- JavaScript, Vue.js, React
- Docker, Git, CI/CD
- REST API, GraphQL

Work Experience:
2019-2024 - Senior PHP Developer, IT Company LLC
- Corporate web applications development
- Database optimization
- Mentoring junior developers

2017-2019 - PHP Developer, StartUp Inc
- MVP products development
- Payment systems integration

Education:
Tashkent University of Information Technologies, 2017
Bachelor, Information Systems

Languages:
- Russian (native)
- English (B2)
- Uzbek (native)

Contacts:
Phone: +998901234567
Email: ivanov@example.com
GitHub: github.com/ivanov
"""

try:
    profile = hr_analyzer.parse_resume(test_resume)
    print("[OK] Resume parsed successfully")
    print()
    print("Candidate Profile:")
    print(f"  Position: {profile.get('position_title', 'N/A')}")
    print(f"  Experience: {profile.get('years_of_experience', 'N/A')} years")
    print(f"  Skills ({len(profile.get('skills', []))}): {', '.join(profile.get('skills', [])[:10])}")
    if profile.get('languages'):
        langs = ', '.join([lang.get('name', '') for lang in profile.get('languages', [])])
        print(f"  Languages: {langs}")
    if profile.get('education'):
        edu = profile.get('education', [])[0]
        print(f"  Education: {edu.get('institution', '')} ({edu.get('year', '')})")
    print()
    print("Full profile JSON:")
    print(json.dumps(profile, indent=2, ensure_ascii=False))
    print()
except Exception as e:
    print(f"[ERROR] {e}")
    import traceback
    traceback.print_exc()
    sys.exit(1)

# Test 2: Match Score Calculation
print()
print("2. MATCH SCORE CALCULATION")
print("-" * 60)

vacancy = {
    'title': 'Senior PHP Developer',
    'must_have_skills': ['PHP', 'Laravel', 'MySQL'],
    'nice_to_have_skills': ['Vue.js', 'Docker', 'PostgreSQL', 'Redis'],
    'min_experience_years': 3,
}

try:
    score = hr_analyzer.calculate_match_score(profile, vacancy)
    breakdown = hr_analyzer.get_match_breakdown(profile, vacancy)

    print(f"[OK] Match Score calculated: {score}%")
    print()
    print("Breakdown:")
    print(json.dumps(breakdown, indent=2, ensure_ascii=False))
    print()
except Exception as e:
    print(f"[ERROR] {e}")
    import traceback
    traceback.print_exc()

# Test 3: Candidate Analysis
print()
print("3. CANDIDATE ANALYSIS")
print("-" * 60)

try:
    analysis = hr_analyzer.analyze_candidate(profile, vacancy)

    print("[OK] Analysis completed")
    print()
    print("Analysis JSON:")
    print(json.dumps(analysis, indent=2, ensure_ascii=False))
    print()

    if analysis.get('strengths'):
        print("Strengths:")
        for i, strength in enumerate(analysis['strengths'], 1):
            print(f"  {i}. {strength}")
        print()

    if analysis.get('weaknesses'):
        print("Weaknesses:")
        for i, weakness in enumerate(analysis['weaknesses'], 1):
            print(f"  {i}. {weakness}")
        print()

    if analysis.get('risks'):
        print("Risks:")
        for i, risk in enumerate(analysis['risks'], 1):
            print(f"  {i}. {risk}")
        print()

    if analysis.get('suggested_questions'):
        print("Recommended questions (top 5):")
        for i, question in enumerate(analysis['suggested_questions'][:5], 1):
            print(f"  {i}. {question}")
        print()

    if analysis.get('recommendation'):
        print(f"Recommendation: {analysis['recommendation']}")
        print()

except Exception as e:
    print(f"[ERROR] {e}")
    import traceback
    traceback.print_exc()

print("="*60)
print("TESTING COMPLETED")
print("="*60)
