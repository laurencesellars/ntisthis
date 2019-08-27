import suds
from suds.client import Client
from suds.wsse import *
import urllib2
import re
import unicodedata
import shutil

all_chars = (unichr(i) for i in xrange(0x110000))
control_chars = ''.join(map(unichr, range(0,32) + range(127,160)))

control_char_re = re.compile('[%s]' % re.escape(control_chars))

def remove_control_chars(s):
	return control_char_re.sub('', s)

url ='https://ws.sandbox.training.gov.au/Deewr.Tga.Webservices/TrainingComponentService.svc?wsdl'
orgurl='https://ws.sandbox.training.gov.au/Deewr.Tga.Webservices/OrganisationService.svc?wsdl'

####################################################
username='WebService.Read'
password='Asdf098'
####################################################

xmlbaseurl='http://training.gov.au/TrainingComponentFiles/'
xmlfolder=os.path.join(os.getcwd(),"itemxml")

client = suds.client.Client(url)
#print client

security = Security()
token = UsernameToken(username, password)
security.tokens.append(token)
client.set_options(wsse=security)

unitdict={}

def get_details(codetoget):
	"""ask the webservice for information on this piece of curriculum"""
	TrainingComponentDetailsRequest= client.factory.create('TrainingComponentDetailsRequest')
	TrainingComponentDetailsRequest.Code=codetoget
	TrainingComponentInformationRequested=client.factory.create('TrainingComponentInformationRequested')
	TrainingComponentInformationRequested.ShowReleases=True
	TrainingComponentInformationRequested.ShowUnitGrid=True
	TrainingComponentInformationRequested.ShowComponents=True
	TrainingComponentDetailsRequest.InformationRequest=TrainingComponentInformationRequested
	return client.service.GetDetails(TrainingComponentDetailsRequest)

def get_xml(codetoget):
	"""Download the xml for a given training component and return the filename """
	xmlfilename = ''
	try:
		TrainingComponentDetailsRequest= client.factory.create('TrainingComponentDetailsRequest')
		TrainingComponentDetailsRequest.Code=codetoget
		TrainingComponentInformationRequested=client.factory.create('TrainingComponentInformationRequested')
		TrainingComponentInformationRequested.ShowReleases=True
		TrainingComponentInformationRequested.ShowUnitGrid=True
		TrainingComponentInformationRequested.ShowFiles=True
		TrainingComponentInformationRequested.ShowComponents=True
		TrainingComponentDetailsRequest.InformationRequest=TrainingComponentInformationRequested
		try:
			result= client.service.GetDetails(TrainingComponentDetailsRequest)
			#print result
			for unit in result.Releases[0]:
				try:
					for fyle in unit.Files.ReleaseFile:
						#print fyle.RelativePath
						fname = fyle.RelativePath.strip()
						fname = fname.lower()
						#sys.stderr.write("\nfile: (%s)" % (fname))
						if fname[-4:]==".xml":
							if os.sep == "/":
								strFile=fyle.RelativePath.replace("\\","/")
							else:
								strFile=fyle.RelativePath
							fullFilePath=os.path.join(xmlfolder, strFile)
							xmlfilename = strFile
							if os.path.exists(os.path.join(xmlfolder, strFile))==False:
								sys.stderr.write('downloading ' + strFile + '\n')
								try:
									print('downloading from ' + xmlbaseurl + urllib2.quote(strFile) + ' to ' +  fullFilePath)
									resp = urllib2.urlopen(xmlbaseurl + urllib2.quote(strFile))
								except (urllib2.URLError, urllib2.HTTPError) as e:
									print 'error:', e
								except IOError,e:
									print "Unexpected io error :", e
								except:
									print "leftover exception";
								try:
									print 'Downloading to ' + os.path.join(xmlfolder, strFile)
									if not os.path.exists(os.path.dirname(fullFilePath)):
										try:
											os.makedirs(os.path.dirname(fullFilePath))
										except:
											print 'oops'
									with open(fullFilePath, 'w+b') as f:
										f.write(resp.read())
									#shutil.copyfile(fullFilePath), os.path.join('newfiles', strFile))
								except TypeError, e:
									print "Unexpected error 55:", e
								return xmlfilename
							else: # file already there
								return xmlfilename
						# if you didn't find an xml file just return an empty string
					return ''
				except NameError,e:
					print e
				except TypeError,e:
					print e
				except IOError,e:
					print 'error ',e
				except:
					print "Unexpected error 1:", sys.exc_info()[0]
					return ''
		except TypeError,e:
			print 'error: ' + e
		except:
			print "Unexpected error 12:", sys.exc_info()[0]
			return ''
	except WebFault, e:
		sys.stderr.write("soap request failed: " + e)
		return ""



#build a search request object
TrainingComponentTypeFilter=client.factory.create('TrainingComponentTypeFilter')
TrainingComponentTypeFilter.IncludeTrainingPackage=True
TrainingComponentSearchRequest=client.factory.create('TrainingComponentSearchRequest')
TrainingComponentSearchRequest.Filter=''    #eg Filter='Animal Care and Management' ie training package name or '' for all
TrainingComponentSearchRequest.IncludeDeleted=False
TrainingComponentSearchRequest.SearchTitle=True
TrainingComponentSearchRequest.SearchCode=False
TrainingComponentSearchRequest.TrainingComponentTypes=TrainingComponentTypeFilter

try:
   result = client.service.Search(TrainingComponentSearchRequest)

   for tp in result.Results[0]:
      xmlfilename = get_xml(tp.Code)

except WebFault, e:
   print "soap request failed:", e